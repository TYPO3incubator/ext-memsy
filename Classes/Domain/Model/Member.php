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

namespace TYPO3Incubator\Memsy\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Incubator\Memsy\Domain\Validator\DateIntervalValidator;
use TYPO3Incubator\Memsy\Domain\Validator\IbanValidator;
use TYPO3Incubator\Memsy\Domain\Validator\PasswordPolicyValidator;
use TYPO3Incubator\Memsy\Domain\Validator\PasswordRepeatValidator;

/**
 * Member
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class Member extends AbstractEntity
{
    protected string $title = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $firstName = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $lastName = '';

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'EmailAddress'])]
    protected string $email = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $telephone = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $address = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $zip = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $city = '';

    #[Validate(['validator' => 'NotEmpty'])]
    protected string $country = '';

    #[Validate([
        'validator' => IbanValidator::class,
        'options' => [
            'propertyThatNeedsToBeSet' => 'sepaDebtorMandateSignDate',
        ],
    ])]
    protected string $iban = '';

    protected string $bic = '';

    protected string $sepaDebtorMandate = '';

    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $sepaDebtorMandateSignDate = null;

    protected string $notes = '';

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'DateTime'])]
    // Interval and message are set by MembershipController::initializeSaveAction
    #[Validate(['validator' => DateIntervalValidator::class])]
    protected ?\DateTime $dateOfBirth = null;

    protected Gender $gender = Gender::NoSelection;

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $privacyAcceptedAt = null;

    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $memberSince = null;

    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $memberUntil = null;

    #[Validate(['validator' => 'NotEmpty'])]
    protected ?Membership $membership = null;

    protected MembershipStatus $membershipStatus = MembershipStatus::Unconfirmed;

    /** @var ObjectStorage<Payment> */
    protected ObjectStorage $payments;

    protected string $username = '';

    #[Validate(['validator' => PasswordPolicyValidator::class])]
    protected string $password = '';

    #[Validate([
        'validator' => PasswordRepeatValidator::class,
        'options' => [
            'propertyToCompare' => 'password',
        ],
    ])]
    protected string $passwordRepeat = '';
    protected string $createHash = '';
    protected int $usergroup = 0;
    protected ?int $pid = 0;
    protected bool $disabled = true;

    public function __construct()
    {
        $this->payments = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): void
    {
        $this->iban = $iban;
    }

    public function getBic(): string
    {
        return $this->bic;
    }

    public function setBic(string $bic): void
    {
        $this->bic = $bic;
    }

    public function getSepaDebtorMandate(): string
    {
        return $this->sepaDebtorMandate;
    }

    public function setSepaDebtorMandate(string $sepaDebtorMandate): void
    {
        $this->sepaDebtorMandate = $sepaDebtorMandate;
    }

    public function getSepaDebtorMandateSignDate(): ?\DateTime
    {
        return $this->sepaDebtorMandateSignDate;
    }

    public function setSepaDebtorMandateSignDate(?\DateTime $sepaDebtorMandateSignDate): void
    {
        $this->sepaDebtorMandateSignDate = $sepaDebtorMandateSignDate;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTime $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    public function getPrivacyAcceptedAt(): ?\DateTime
    {
        return $this->privacyAcceptedAt;
    }

    public function setPrivacyAcceptedAt(?\DateTime $privacyAcceptedAt): void
    {
        $this->privacyAcceptedAt = $privacyAcceptedAt;
    }

    public function getMemberSince(): ?\DateTime
    {
        return $this->memberSince;
    }

    public function setMemberSince(?\DateTime $memberSince): void
    {
        $this->memberSince = $memberSince;
    }

    public function getMemberUntil(): ?\DateTime
    {
        return $this->memberUntil;
    }

    public function setMemberUntil(?\DateTime $memberUntil): void
    {
        $this->memberUntil = $memberUntil;
    }

    public function getMembership(): ?Membership
    {
        return $this->membership;
    }

    public function setMembership(?Membership $membership): void
    {
        $this->membership = $membership;
    }

    public function getMembershipStatus(): MembershipStatus
    {
        return $this->membershipStatus;
    }

    public function setMembershipStatus(MembershipStatus $membershipStatus): void
    {
        $this->membershipStatus = $membershipStatus;
    }

    public function getPayments(): ObjectStorage
    {
        return $this->payments;
    }

    public function setPayments(ObjectStorage $payments): void
    {
        $this->payments = $payments;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(#[\SensitiveParameter] string $password): void
    {
        $this->password = $password;
    }

    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    public function setPasswordRepeat(#[\SensitiveParameter] string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getCreateHash(): string
    {
        return $this->createHash;
    }

    public function setCreateHash(string $createHash): void
    {
        $this->createHash = $createHash;
    }

    public function getUsergroup(): int
    {
        return $this->usergroup;
    }

    public function setUsergroup(int $usergroup): void
    {
        $this->usergroup = $usergroup;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(?int $pid): void
    {
        $this->pid = $pid;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function getMembershipStatusLabel(): string
    {
        $badge = fn (string $severity) => sprintf(
            '<span class="badge badge-%s">%s</span>',
            $severity,
            $this->membershipStatus->label(),
        );

        return match ($this->membershipStatus) {
            MembershipStatus::Unconfirmed => $badge('notice'),
            MembershipStatus::Pending => $badge('warning'),
            MembershipStatus::Active => $badge('success'),
            MembershipStatus::Inactive => $badge('danger'),
        };
    }

    public function getLastPayment(): ?Payment
    {
        return array_reduce(
            $this->getPayments()->toArray(),
            static function (?Payment $latest, Payment $current): ?Payment {
                return $latest === null || $current->getPaidAt() > $latest->getPaidAt()
                    ? $current
                    : $latest;
            },
            null
        );
    }
}
