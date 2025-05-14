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

namespace TYPO3Incubator\Memsy\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Repository\MemberRepository;

/**
 * ManageMembersCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[AsCommand(
    'memsy:manage-members',
    'Manage members',
)]
final class ManageMembersCommand extends Command
{
    private const string ALL_SITES = 'all';

    private SymfonyStyle $io;

    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly SiteFinder $siteFinder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'site',
            's',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'The sites for which to manage members (root page id or site identifier)',
            [self::ALL_SITES],
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sites = $input->getOption('site');

        // Resolve sites by root page id or identifier
        if (in_array(self::ALL_SITES, $sites, true)) {
            $sites = $this->siteFinder->getAllSites();
        } else {
            $sites = array_map(
                static fn (string|int $site) => is_numeric($site)
                    ? $this->siteFinder->getSiteByRootPageId((int) $site)
                    : $this->siteFinder->getSiteByIdentifier($site),
                $sites,
            );
        }

        foreach ($sites as $site) {
            $members = $this->memberRepository->findDeregisteredBySite($site);
            $results = [];

            /** @var Member $member */
            foreach ($members as $member) {
                $member->setDisabled(true);
                $results[] = $member;

                $this->persistenceManager->update($member);
            }

            $this->io->title(
                sprintf('Site: %s [%d]', $site->getIdentifier(), $site->getRootPageId()),
            );

            if ($results !== []) {
                $this->io->success('The following members were disabled:');
                $this->io->listing(
                    array_map($this->mapMemberToListing(...), $results),
                );
            } else {
                $this->io->comment('Nothing to do here. Enjoy your cold drinks!');
            }
        }

        $this->persistenceManager->persistAll();

        return self::SUCCESS;
    }

    private function mapMemberToListing(Member $member): string
    {
        return sprintf('%s, %s [%s]', $member->getLastName(), $member->getFirstName(), $member->getUid());
    }
}
