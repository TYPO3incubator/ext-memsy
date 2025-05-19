<?php

/*
 * This file is part of the Member Management project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use TYPO3Incubator\Memsy\Controller\BackendMemberController;
use TYPO3Incubator\Memsy\Extension;

return [
    'memsy_membermanagement' => [
        'parent' => 'web',
        'position' => ['after' => 'web_list'],
        'access' => 'user',
        'workspaces' => 'live',
        'labels' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_mod_member.xlf',
        'navigationComponent' => '@typo3/backend/tree/page-tree-element',
        'extensionName' => Extension::NAME,
        'iconIdentifier' => 'tx-memsy-member-management-module',
        'controllerActions' => [
            BackendMemberController::class => [
                'index',
                'generateSepaXml',
                'memberBulkAction',
                'sendPaymentReminder',
            ],
        ],
    ],
];
