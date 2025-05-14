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

namespace TYPO3Incubator\Memsy\Service;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Model\MembershipStatus;
use TYPO3Incubator\Memsy\Domain\Repository\MemberRepository;
use TYPO3Incubator\Memsy\Exception\MemberIsAlreadyConfirmed;
use TYPO3Incubator\Memsy\Exception\MemberIsAlreadyCreated;
use TYPO3Incubator\Memsy\Exception\MemberIsNoLongerInAnActiveMembership;
use TYPO3Incubator\Memsy\Exception\MemberIsNotProperlyPersisted;

/**
 * MembershipService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class MembershipService
{
    private ?ServerRequestInterface $request = null;

    public function __construct(
        private readonly EmailService $emailService,
        private readonly HashService $hashService,
        private readonly LoggerInterface $logger,
        private readonly PaymentService $paymentService,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly MemberRepository $memberRepository,
    ) {
    }

    /**
     * @throws MemberIsAlreadyConfirmed
     * @throws MemberIsAlreadyCreated
     * @throws MemberIsNoLongerInAnActiveMembership
     * @throws MemberIsNotProperlyPersisted
     */
    public function create(Member $member, int $confirmationPid = 0): bool
    {
        $uid = $member->getUid();
        $email = $member->getEmail();

        // Throw on invalid member(ship) state
        if ($uid === null || $email === '') {
            throw new MemberIsNotProperlyPersisted();
        }
        if ($member->getCreateHash() !== '') {
            throw new MemberIsAlreadyCreated($member);
        }
        if ($member->getMembershipStatus() !== MembershipStatus::Unconfirmed) {
            throw new MemberIsAlreadyConfirmed($member);
        }
        if ($member->getMemberUntil()?->getTimestamp() > time()) {
            throw new MemberIsNoLongerInAnActiveMembership($member);
        }

        // Create and attach hash
        $hash = $this->hashService->hmac((string) $uid, $email);
        $member->setCreateHash($hash);

        // Persist updated member
        $this->persistenceManager->update($member);
        $this->persistenceManager->persistAll();

        // Send mail to new member
        $email = $this->emailService->createEmail(
            'CreateMembership',
            'LLL:EXT:memsy/Resources/Private/Language/locallang.xlf:email.createMembership.subject',
            $member,
        );
        $email->assignMultiple([
            'confirmationPid' => $confirmationPid,
            'hash' => $hash,
        ]);

        try {
            return $this->emailService->sendEmail($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'Error while sending membership double-opt-in mail: {message}',
                ['message' => $exception->getMessage()],
            );

            return false;
        }
    }

    /**
     * @throws MemberIsAlreadyConfirmed
     * @throws MemberIsNoLongerInAnActiveMembership
     */
    public function confirm(Member $member): bool
    {
        $managerName = $this->getSiteSettings()?->get('memsy.organization.personInCharge');
        $managerEmailAddress = $this->getSiteSettings()?->get('memsy.organization.emailOfPersonInCharge');

        if ($member->getMembershipStatus() !== MembershipStatus::Unconfirmed) {
            throw new MemberIsAlreadyConfirmed($member);
        }
        if ($member->getMemberUntil()?->getTimestamp() > time()) {
            throw new MemberIsNoLongerInAnActiveMembership($member);
        }

        // Confirm membership
        $member->setCreateHash('');
        $member->setMembershipStatus(MembershipStatus::Pending);

        // Update member in database
        $this->persistenceManager->update($member);
        $this->persistenceManager->persistAll();

        // Early return if no manager email address is configured
        if (!is_string($managerEmailAddress) || trim($managerEmailAddress) === '') {
            $this->logger->warning(
                'No email address configured for person in charge of member management, manager email skipped.',
            );

            return true;
        }

        $email = $this->emailService->createEmail(
            'NewMembership',
            'LLL:EXT:memsy/Resources/Private/Language/locallang.xlf:email.newMembership.subject',
            $member,
            new Address($managerEmailAddress, $managerName),
        );

        $email->assign('beMemberPid', $this->getSiteSettings()?->get('felogin.pid'));

        try {
            return $this->emailService->sendEmail($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'Error while sending membership confirmation mail to manager: {message}',
                ['message' => $exception->getMessage()],
            );

            return false;
        }

        return $this->mailer->getSentMessage() !== null;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function cancel(Member $member): bool
    {
        if ($member->getMembershipStatus() === MembershipStatus::Inactive) {
            $this->logger->error(
                'Member status is already inactive',
            );
            return false;
        }

        // Set member to cancel by setting it inactive and setting a member_until date
        $this->setMembersInactive([$member->getUid()]);
        // @todo no hardcoded date → will be done with payment logic
        $member->setMemberUntil(new \DateTime('1.9.2026'));

        try {
            $this->persistenceManager->update($member);
        } catch (UnknownObjectException $exception) {
            $this->logger->error(
                'Error while updating the member field member_until: {message}',
                ['message' => $exception->getMessage()],
            );
            return false;
        }

        // Confirmation mail
        $memberConfirmationEmail = $this->emailService->createEmail(
            'CancelMembershipConfirmation',
            'LLL:EXT:memsy/Resources/Private/Language/locallang.xlf:email.cancelMembershipConfirmation.subject',
            $member,
        );

        // Mail to person in charge
        $managerName = $this->getSiteSettings()?->get('memsy.organization.personInCharge');
        $managerEmailAddress = $this->getSiteSettings()?->get('memsy.organization.emailOfPersonInCharge');
        $memberInfoEmail = $this->emailService->createEmail(
            'CanceledMembership',
            'LLL:EXT:memsy/Resources/Private/Language/locallang.xlf:email.canceledMembership.subject',
            $member,
            new Address($managerEmailAddress, $managerName)
        );

        if ($this->request !== null) {
            $memberConfirmationEmail->setRequest($this->request);
            $memberInfoEmail->setRequest($this->request);
        }

        try {
            $this->emailService->sendEmail($memberConfirmationEmail);
            $this->emailService->sendEmail($memberInfoEmail);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(
                'Error while sending cancellation membership mail: {message}',
                ['message' => $exception->getMessage()],
            );
            return false;
        }

        return true;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
        $this->emailService->setRequest($request);
    }

    private function getSiteSettings(): ?SiteSettings
    {
        $site = $this->request?->getAttribute('site');

        if (!($site instanceof Site)) {
            return null;
        }

        return $site->getSettings();
    }

    public function setMembersActive(array $memberUids) {
        foreach ($memberUids as $memberUid) {
            $member = $this->memberRepository->findByUid($memberUid);
            if ($member->getMembershipStatus() === MembershipStatus::Active) {
                continue;
            }
            $member->setMembershipStatus(MembershipStatus::Active);
            $member->setDisabled(false);
            $this->memberRepository->update($member);

            $email = $this->emailService->createEmail(
                'MembershipActivated',
                'LLL:EXT:memsy/Resources/Private/Language/locallang.xlf:email.membershipActivated.subject',
                $member,
            );

            $email->assign('sitesets', $this->getSiteSettings()->getAll());

            // Create first payment
            $this->paymentService->createPayment($member, true);

            try {
                $this->emailService->sendEmail($email);
            } catch (TransportExceptionInterface $exception) {
                $this->logger->error(
                    'Error while sending new membership activation mail: {message}',
                    ['message' => $exception->getMessage()],
                );
            }
        }
    }

    /**
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    public function setMembersInactive(array $memberUids): void
    {
        foreach ($memberUids as $memberUid) {
            $member = $this->memberRepository->findByUid($memberUid);
            if (!$member) {
                continue;
            }

            if ($member->getMembershipStatus() === MembershipStatus::Inactive) {
                continue;
            }
            $member->setMembershipStatus(MembershipStatus::Inactive);
            $member->setDisabled(true);
            $this->memberRepository->update($member);
            $this->paymentService->cancelPendingPayments($member);
        }
        $this->persistenceManager->persistAll();
    }

    private function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;

        if ($backendUser instanceof BackendUserAuthentication) {
            return $backendUser;
        }

        return null;
    }
}
