<?php

use TYPO3Incubator\Memsy\Domain\Model\PaymentState;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment',
        'label' => 'member',
        'label_alt' => 'paid_at, amount',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'iconfile' => 'EXT:memsy/Resources/Public/Icons/payment.svg',
    ],
    'types' => [
        1 => ['showitem' => 'member, paid_at, amount, state, reminder_mail_sent_at'],
    ],
    'columns' => [
        'member' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.member',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
                'required' => true,
            ],
        ],
        'paid_at' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.paid_at',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'required' => true,
            ],
        ],
        'due_by' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.due_by',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'required' => true,
            ],
        ],
        'amount' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.amount',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'double2',
                'default' => 0.00,
                'required' => true,
            ],
        ],
        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'items' => [
                    [
                        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.state.pending',
                        'value' => PaymentState::Pending->value,
                    ],
                    [
                        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.state.paid',
                        'value' => PaymentState::Paid->value,
                    ],
                    [
                        'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.state.cancelled',
                        'value' => PaymentState::Cancelled->value,
                    ],
                ],
                'default' => PaymentState::Pending->value,
            ],
        ],
        'reminder_mail_sent_at' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_payment.reminder_mail_sent_at',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
            ],
        ],
    ],
];
