<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Incubator\Memsy\Domain\Model\MembershipStatus;
use TYPO3Incubator\Memsy\TCA\TypeMembershipItemsProcFunc;

defined('TYPO3') || die();

if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumns_tx_memsy_fe_users = [];
    $tempColumns_tx_memsy_fe_users[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = [
        'exclude' => true,
        'label'   => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.tx_memsy_member',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.tx_memsy_member.member', 'value' => 'tx_memsy_member'],
            ],
            'default' => 'tx_memsy_member',
            'size' => 1,
            'maxitems' => 1,
        ],
    ];
    ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns_tx_memsy_fe_users);
}

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    $GLOBALS['TCA']['fe_users']['ctrl']['type'],
    '',
    'after:' . $GLOBALS['TCA']['fe_users']['ctrl']['label']
);

$tmp_memsy_columns = [
    'date_of_birth' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.date_of_birth',
        'config' => [
            'type' => 'datetime',
            'format' => 'date',
            'default' => 0,
        ],
    ],
    'gender' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.gender',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.gender.noSelection', 'value' => -1],
                ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.gender.other', 'value' => 0],
                ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.gender.male', 'value' => 1],
                ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.gender.female', 'value' => 2],
            ],
            'default' => -1,
            'size' => 1,
            'maxitems' => 1,
        ],
    ],
    'iban' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.iban',
        'config' => [
            'type' => 'input',
            'size' => 36,
            'eval' => 'trim',
        ],
    ],
    'bic' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.bic',
        'config' => [
            'type' => 'input',
            'size' => 11,
            'eval' => 'trim',
        ],
    ],
    'sepa_debtor_mandate' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.sepa_debtor_mandate',
        'config' => [
            'type' => 'input',
            'size' => 11,
            'eval' => 'trim',
            'max' => 35
        ],
    ],
    'sepa_debtor_mandate_sign_date' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.sepa_debtor_mandate_sign_date',
        'config' => [
            'type' => 'datetime',
            'default' => 0,
        ],
    ],
    'privacy_accepted_at' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.privacy_accepted_at',
        'config' => [
            'type' => 'datetime',
            'default' => 0,
        ],
    ],
    'member_since' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.member_since',
        'config' => [
            'type' => 'datetime',
            'default' => 0,
        ],
    ],
    'member_until' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.member_until',
        'config' => [
            'type' => 'datetime',
            'default' => 0,
        ],
    ],
    'membership' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'minitems' => 0,
            'maxitems' => 1,
            'foreign_table' => 'tx_memsy_domain_model_membership',
            'itemsProcFunc' => TypeMembershipItemsProcFunc::class . '->itemsProcFunc',
        ],
    ],
    'membership_status' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership_status',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'minitems' => 1,
            'maxitems' => 1,
            'items' => [
                [
                    'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership_status.unconfirmed',
                    'value' => MembershipStatus::Unconfirmed->value,
                ],
                [
                    'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership_status.pending',
                    'value' => MembershipStatus::Pending->value,
                ],
                [
                    'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership_status.active',
                    'value' => MembershipStatus::Active->value,
                ],
                [
                    'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.membership_status.inactive',
                    'value' => MembershipStatus::Inactive->value,
                ],
            ],
            'default' => MembershipStatus::Unconfirmed->value,
        ],
    ],
    'payments' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.payments',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_memsy_domain_model_payment',
            'foreign_field' => 'member',
            'maxitems' => 9999,
            'appearance' => [
                'collapseAll' => true,
                'levelLinksPosition' => 'bottom',
            ],
        ],
    ],
    'notes' => [
        'exclude' => true,
        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.notes',
        'config' => [
            'type' => 'text',
            'cols' => 30,
            'rows' => 5,
            'eval' => 'trim',
        ],
    ],
    'create_hash' => [
        'exclude' => true,
        'label' => 'Create hash',
        'config' => [
            'type' => 'input',
            'size' => 255,
            'readOnly' => true,
            'eval' => 'trim',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('fe_users', $tmp_memsy_columns);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'date_of_birth, gender, iban, bic, sepa_debtor_mandate, sepa_debtor_mandate_sign_date, privacy_accepted_at, member_since, member_until, membership, membership_status, payments, notes, create_hash',
);

$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = ['label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:fe_users.tx_extbase_type.tx_memsy_member', 'value' => 'tx_memsy_member'];
$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['default'] = 'tx_memsy_member';
