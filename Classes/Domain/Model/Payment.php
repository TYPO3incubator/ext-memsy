<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "memsy".
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

class Payment extends AbstractEntity
{
    protected Member $member;

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $paidAt = null;

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'DateTime'])]
    protected ?\DateTime $dueBy = null;

    #[Validate(['validator' => 'NotEmpty'])]
    #[Validate(['validator' => 'Float'])]
    protected float $amount = 0.0;

    protected PaymentState $state = PaymentState::Pending;

    #[Validate(['validator' => 'NotEmpty'])]
    protected ?\DateTime $reminderMailSentAt = null;

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): void
    {
        $this->member = $member;
    }

    public function getPaidAt(): ?\DateTime
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTime $paidAt): void
    {
        $this->paidAt = $paidAt;
    }

    public function getDueBy(): ?\DateTime
    {
        return $this->dueBy;
    }

    public function setDueBy(?\DateTime $dueBy): void
    {
        $this->dueBy = $dueBy;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getState(): PaymentState
    {
        return $this->state;
    }

    public function setState(PaymentState $state): void
    {
        $this->state = $state;
    }

    public function getReminderMailSentAt(): ?\DateTime
    {
        return $this->reminderMailSentAt;
    }

    public function setReminderMailSentAt(?\DateTime $reminderMailSentAt): void
    {
        $this->reminderMailSentAt = $reminderMailSentAt;
    }

    public function getStateLabel(): string
    {
        $now = new \DateTimeImmutable();
        if ($this->state === PaymentState::Pending && $this->dueBy && $this->dueBy < $now) {
            return 'Overdue';
        }
        return $this->state->label();
    }
}
