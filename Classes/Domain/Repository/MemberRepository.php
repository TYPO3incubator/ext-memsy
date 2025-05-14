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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Model\MembershipStatus;

/**
 * MemberRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends Repository<Member>
 */
final class MemberRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();

        $request = $this->getServerRequest();

        // if call comes from backend -> ignore enable fields
        if ($request === null || ApplicationType::fromRequest($request)->isBackend()) {
            $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
            $querySettings->setIgnoreEnableFields(true);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

    public function findOneByHash(string $hash, Site $site, bool $includeDisabled = false): ?Member
    {
        $query = $this->createQueryForSite($site);

        if ($includeDisabled) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
        }

        $query->matching(
            $query->equals('createHash', $hash),
        );

        return $query->execute()->getFirst();
    }

    public function findActiveInFolder(int $folderId): array
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds([$folderId]);

        $query = $query->matching(
            $query->equals('membership_status', MembershipStatus::Active),
        );

        return $query->execute()->toArray();
    }

    public function findConfirmed(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields( true);

        $query->matching(
            $query->logicalNot(
                $query->equals('membershipStatus', MembershipStatus::Unconfirmed),
            ),
        );

        return $query->execute();
    }

    /**
     * @return QueryResultInterface<Member>
     */
    public function findBySite(Site $site): QueryResultInterface
    {
        return $this->createQueryForSite($site)->execute();
    }

    public function findDeregisteredBySite(Site $site): QueryResultInterface
    {
        $query = $this->createQueryForSite($site);
        $query->matching(
            $query->lessThanOrEqual('memberUntil', new \DateTime()),
        );

        return $query->execute();
    }

    private function createQueryForSite(Site $site): QueryInterface
    {
        $storagePid = $site->getSettings()->get('felogin.pid');
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds([$storagePid]);

        return $query;
    }

    public function findByFilters(array $filters = [], array $orderings = [], $membersPid = 0): QueryResultInterface
    {
        $query = $this->createQuery();

        if ($membersPid > 0) {
            $query->getQuerySettings()->setStoragePageIds([$membersPid]);
        }

        $constraints = [
            $query->greaterThan('membershipStatus', MembershipStatus::Unconfirmed),
        ];

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $constraints[] = $query->logicalOr(
                $query->like('first_name', $term),
                $query->like('last_name', $term),
                $query->like('email', $term)
            );
        }

        if (!empty($filters['membershipUid']) && $filters['membershipUid'] > 0) {
            $constraints[] = $query->equals('membership.uid', (int)$filters['membershipUid']);
        }

        if (isset($filters['membershipStatus']) && $filters['membershipStatus'] > -1) {
            $constraints[] = $query->equals('membership_status', (int)$filters['membershipStatus']);
        }

        $query->matching($query->logicalAnd(...$constraints));
        if ($orderings !== []) {
            $query->setOrderings($orderings);
        }
        return $query->execute();
    }

    #[\Override]
    public function findByUid($uid, int $storagePid = 0): ?Member
    {
        $query = $this->createQuery();
        if ($storagePid > 0) {
            $query->getQuerySettings()->setStoragePageIds([$storagePid]);
        } else {
            $query->getQuerySettings()->setRespectStoragePage(false);
        }
        $query->matching($query->equals('uid', $uid));
        return $query->execute()->getFirst();
    }

    public function findAll(int $storagePid = 0): array|QueryResultInterface
    {
        $query = $this->createQuery();
        if ($storagePid > 0) {
            $query->getQuerySettings()->setStoragePageIds([$storagePid]);
        } else {
            $query->getQuerySettings()->setRespectStoragePage(false);
        }
        return $query->execute();
    }

    private function getServerRequest(): ?ServerRequestInterface
    {
        $serverRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if (!($serverRequest instanceof ServerRequestInterface)) {
            return $serverRequest;
        }

        return null;
    }
}
