<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;
use TYPO3Incubator\Memsy\Controller\MembershipController;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'Memsy',
    'CreateMembership',
    [
        MembershipController::class => 'create, save',
    ],
    [
        MembershipController::class => 'create, save',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'Memsy',
    'ConfirmMembership',
    [
        MembershipController::class => 'confirm',
    ],
    [
        MembershipController::class => 'confirm',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

ExtensionUtility::configurePlugin(
    'Memsy',
    'MembershipSettings',
    [
        MembershipController::class => 'edit, cancel',
    ],
    [
        MembershipController::class => 'edit, cancel',
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1746439249]
    = 'EXT:memsy/Resources/Private/Templates/Email/';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables']['fe_users'] = [
    'dateField' => 'member_until',
    'expirePeriod' => 90,
];
