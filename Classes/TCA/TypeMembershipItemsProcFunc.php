<?php

namespace TYPO3Incubator\Memsy\TCA;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3Incubator\Memsy\Domain\Model\Membership;
use TYPO3Incubator\Memsy\Domain\Repository\MembershipRepository;

final readonly class TypeMembershipItemsProcFunc
{
    public function __construct(
        private readonly MembershipRepository $membershipRepository,
        private readonly SiteFinder $siteFinder
    ) {
    }

    public function itemsProcFunc(&$params): void
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($params['effectivePid']);
            $membershipPid = $site->getSettings()->get('memsy.storage.membershipsFolderPid');
        } catch (\Exception) {
            return;
        }

        $memberships = $this->membershipRepository->findAllByStorageId($membershipPid)->toArray();
        /** @var Membership $membership */
        foreach ($memberships as $membership) {
            $params['items'][] = [
                'label' => $membership->getTitle(),
                'value' => $membership->getUid(),
            ];
        }
    }
}
