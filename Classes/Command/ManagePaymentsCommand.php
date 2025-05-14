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
use TYPO3Incubator\Memsy\Domain\Model\Member;
use TYPO3Incubator\Memsy\Domain\Repository\MemberRepository;
use TYPO3Incubator\Memsy\Payment\PaymentManagementAction;
use TYPO3Incubator\Memsy\Payment\PaymentManagementResult;
use TYPO3Incubator\Memsy\Service\PaymentService;

/**
 * ManagePaymentsCommand
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[AsCommand(
    'memsy:manage-payments',
    'Create, update and delete recurring membership payments',
)]
final class ManagePaymentsCommand extends Command
{
    private const string ALL_SITES = 'all';

    private SymfonyStyle $io;

    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly PaymentService $paymentService,
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
            'The sites for which to manage membership payments (root page id or site identifier)',
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
            $members = $this->memberRepository->findBySite($site);
            $results = [];

            /** @var Member $member */
            foreach ($members as $member) {
                $result = $this->paymentService->processMemberPayments($member);

                if ($result->action !== PaymentManagementAction::Nothing || $output->isVerbose()) {
                    $results[] = $result;
                }
            }

            $this->io->title(
                sprintf('Site: %s [%d]', $site->getIdentifier(), $site->getRootPageId()),
            );

            if ($results !== []) {
                $this->io->table(
                    ['Member', 'Action', 'Payment'],
                    array_map($this->mapResultToTableRow(...), $results),
                );
            } else {
                $this->io->comment('Nothing to do here. Enjoy your beer!');
            }
        }

        return self::SUCCESS;
    }

    private function mapResultToTableRow(PaymentManagementResult $result): array
    {
        return [
            sprintf('%s, %s [%d]', $result->member->getLastName(), $result->member->getFirstName(), $result->member->getUid()),
            match ($result->action) {
                PaymentManagementAction::ManualActionRequired => '<error>Manual action required</error>',
                PaymentManagementAction::NewPaymentCreated => '<info>New payment created</info>',
                PaymentManagementAction::Nothing => 'Nothing to do',
                PaymentManagementAction::ReminderMailCouldNotBeSent => '<error>Error while sending reminder mail</error>',
                PaymentManagementAction::ReminderMailSent => '<comment>Reminder mail sent</comment>',
            },
            match ($result->payment) {
                null => 'No associated payment',
                default => sprintf('%01.2f €, due by %s', $result->payment->getAmount(), $result->payment->getDueBy()?->format('d.m.Y')),
            },
        ];
    }
}
