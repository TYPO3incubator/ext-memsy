<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "memsy".
 *
 * Copyright (C) 2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace TYPO3Incubator\Memsy\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Uid\Uuid;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Model\MembershipStatus;
use TYPO3Incubator\Memsy\Domain\Repository\MemberRepository;
use TYPO3Incubator\Memsy\Domain\Repository\MembershipRepository;
use TYPO3Incubator\Memsy\Exception\Exception;
use TYPO3Incubator\Memsy\Service\MembershipService;

/**
 * MembershipController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class MembershipController extends ActionController
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly MembershipRepository $membershipRepository,
        private readonly MembershipService $membershipService,
        private readonly PasswordHashFactory $passwordHashFactory,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {}

    protected function initializeAction(): void
    {
        $this->membershipService->setRequest($this->request);
    }

    protected function initializeCreateAction(): void
    {
        // Allow "member" only as internal argument when forwarding from "save" action
        if ($this->request->hasArgument('member') &&
            !($this->request->getArgument('member') instanceof Member)
        ) {
            $this->request->withArgument('member', null);
        }
    }

    protected function createAction(?Member $member = null): ResponseInterface
    {
        // Get memberships by pid
        $siteSettings = $this->request->getAttribute('site')->getSettings();
        $membershipPid = $siteSettings->get('memsy.storage.membershipsFolderPid');
        $memberships = ($membershipPid !== null) ? $this->membershipRepository->findAllByStorageId((int)$membershipPid)->toArray() : [];

        $this->view->assignMultiple([
            'currentDateFormatted' => (new \DateTimeImmutable())->format(\DateTimeInterface::W3C),
            'member' => $member ?? new Member(),
            'memberships' => $memberships,
            'sitesets' => $this->request->getAttribute('site')->getSettings()->getAll(),
            'data' => $this->getContentObjectData(),
        ]);

        return $this->htmlResponse();
    }

    protected function initializeSaveAction(): void
    {
        $this->arguments->getArgument('member')
            ->getPropertyMappingConfiguration()
            ->forProperty('dateOfBirth')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d',
            )
        ;
    }

    protected function saveAction(Member $member): ResponseInterface
    {
        $member->setPrivacyAcceptedAt(new \DateTime());

        if ($member->getSepaDebtorMandateSignDate()) {
            // Max allowed characters for mandate reference number is 35
            // UUID v4 is 128 numbers, hex is 32 hex numbers
            $newRandomMandateReferenceNumber = strtoupper(UUID::v4()->toHex());
            $member->setSepaDebtorMandate($newRandomMandateReferenceNumber);
            $member->setSepaDebtorMandateSignDate(new \DateTime());
        } else {
            $member->setSepaDebtorMandateSignDate(null);
        }

        // Hash given password
        $member->setPassword(
            $this->passwordHashFactory->getDefaultHashInstance('FE')->getHashedPassword($member->getPassword()),
        );

        // set pid and usergroup based on site settings
        $siteSettings = $this->request->getAttribute('site')->getSettings();
        $usergroup = (int) $siteSettings->get('memsy.organization.defaultUsergroup');
        $pid = (int) $siteSettings->get('felogin.pid');
        $member->setUsergroup($usergroup);
        $member->setPid($pid);

        // Reset password repeat since we don't need it anymore
        $member->setPasswordRepeat('');
        $member->setUsername($member->getEmail());

        // Disable member until consent was given
        $member->setDisabled(true);
        $member->setMembershipStatus(MembershipStatus::Unconfirmed);

        $this->persistenceManager->add($member);
        $this->persistenceManager->persistAll();

        $data = $this->getContentObjectData();
        $confirmationPid = (int)($data['tx_memsy_confirmation_pid'] ?? 0);

        try {
            $created = $this->membershipService->create($member, $confirmationPid);
        } catch (Exception $exception) {
            $created = false;

            // @todo Use better error message, not only exception message
            $this->addFlashMessage($exception->getMessage());
        }

        if ($created) {
            return $this->htmlResponse();
        }

        // Remove already persisted member if membership could not be created
        $this->persistenceManager->remove($member);
        $this->persistenceManager->persistAll();

        // Obfuscate submitted password for rendering in frontend
        $member->setPassword('');

        return (new ForwardResponse('create'))->withArguments([
            'member' => $member
        ]);
    }

    protected function confirmAction(string $hash, string $email): ResponseInterface
    {
        /** @var Site $site */
        $site = $this->request->getAttribute('site');
        $member = $this->memberRepository->findOneByHash($hash, $site, true);

        // Show error if no member with associated hash is found
        if ($member === null) {
            return $this->errorResponse('memberNotFound', 404);
        }

        // Show error if email does not match
        if ($member->getEmail() !== $email) {
            return $this->errorResponse('invalidEmailAddress');
        }

        try {
            if (!$this->membershipService->confirm($member)) {
                return $this->errorResponse('unknown');
            }
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getCode());
        }

        return $this->htmlResponse();
    }

    protected function editAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function cancelAction(): ResponseInterface
    {
        /** @var FrontendUserAuthentication $user */
        $user = $this->request->getAttribute('frontend.user');
        $userId = $user->getUserId();

        // No user is logged in
        if ($userId === null) {
            return $this->redirectToUri('/');
        }

        $member = $this->memberRepository->findByUid($user->getUserId());
        if ($member) {
            $this->membershipService->cancel($member);
        }

        return $this->htmlResponse();
    }

    private function errorResponse(string $reason, int $statusCode = 400): ResponseInterface
    {
        $this->view->assign('error', $reason);

        $response = $this->htmlResponse(
            $this->view->render('Error'),
        );

        return $response->withStatus($statusCode);
    }

    /**
     * @return array<string, mixed>
     */
    private function getContentObjectData(): array
    {
        $contentObject = $this->request->getAttribute('currentContentObject');

        if (!($contentObject instanceof ContentObjectRenderer)) {
            return [];
        }

        return $contentObject->data;
    }
}
