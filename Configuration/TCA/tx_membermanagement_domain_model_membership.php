<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_membership',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'iconfile' => 'EXT:memsy/Resources/Public/Icons/membership.svg',
    ],
    'types' => [
        1 => ['showitem' => 'title, description, price'],
    ],
    'columns' => [
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_membership.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_membership.description',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'price' => [
            'exclude' => true,
            'label' => 'LLL:EXT:memsy/Resources/Private/Language/locallang_db.xlf:tx_memsy_domain_model_membership.price',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'double2',
                'default' => 0.00,
            ],
        ],
    ],
];
