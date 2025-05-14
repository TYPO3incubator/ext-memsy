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

namespace TYPO3Incubator\Memsy\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Model\Payment;

/**
 * PaymentRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends Repository<Payment>
 */
final class PaymentRepository extends Repository
{
    protected $defaultOrderings = [
        'dueBy' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @param array<Member> $members
     * @return array<Member>
     * @throws InvalidQueryException
     */
    public function findMembersWithOpenPayments(int $folderId, array $members, int $dueDateYearTimestamp): array
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds([$folderId]);

        $query = $query->matching(
            $query->logicalAnd(
                $query->in('member', $members),
                $query->lessThan('paid_at', $dueDateYearTimestamp),
            ),
        );

        $paymentsFromMembersWithPaidMembershipPayments = $query->execute()->toArray();

        $membersWithPaidMembershipPayments = array_map(static fn(Payment $payment) => $payment->getMember(), $paymentsFromMembersWithPaidMembershipPayments);

        $membersWithOpenPayments = array_filter($members, static function (Member $member) use ($membersWithPaidMembershipPayments) {
            $membershipPrice = $member->getMembership()?->getPrice();
            if (!$membershipPrice || $membershipPrice === 0.0) {
                return false;
            }

            return !in_array($member->getUid(), $membersWithPaidMembershipPayments, true);
        });

        return $membersWithOpenPayments;
    }

    /**
     * @return QueryResultInterface<Payment>
     */
    public function findBySite(Site $site): QueryResultInterface
    {
        return $this->createQueryForSite($site)->execute();
    }

    /**
     * @return QueryResultInterface<Payment>
     */
    public function findByMember(Member $member): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('member', $member),
        );

        return $query->execute();
    }

    private function createQueryForSite(Site $site): QueryInterface
    {
        $storagePid = $site->getSettings()->get('memsy.storage.paymentsFolderPid');
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds([$storagePid]);

        return $query;
    }
}
