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

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3Incubator\Memsy\Domain\Model\Member;

/**
 * EmailService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class EmailService
{
    private LanguageService $languageService;
    private ?ServerRequestInterface $request = null;

    public function __construct(
        private readonly LanguageServiceFactory $languageServiceFactory,
        private readonly MailerInterface $mailer,
    ) {
        $this->languageService = $this->languageServiceFactory->createFromUserPreferences($this->getBackendUserAuthentication());
    }

    public function createEmail(
        string $template,
        string $subject,
        Member $member,
        ?Address $recipient = null,
    ): FluidEmail {
        if ($recipient === null) {
            $recipient = new Address(
                $member->getEmail(),
                $member->getFirstName() . ' ' . $member->getLastName(),
            );
        }

        $email = new FluidEmail();
        $email
            ->to($recipient)
            ->subject($this->languageService->sL($subject))
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate($template)
            ->assign('member', $member)
        ;

        if ($this->request !== null) {
            $email->setRequest($this->request);
        }

        return $email;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(FluidEmail $email): bool
    {
        $this->mailer->send($email);

        return $this->mailer->getSentMessage() !== null;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;

        $siteLanguage = $request->getAttribute('language');

        if ($siteLanguage instanceof SiteLanguage) {
            $this->languageService = $this->languageServiceFactory->createFromSiteLanguage($siteLanguage);
        }
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
