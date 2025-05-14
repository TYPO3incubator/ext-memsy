<?php

declare(strict_types=1);

use TYPO3Incubator\Memsy\Domain\Model\Member;

return [
    Member::class => [
        'tableName' => 'fe_users',
        'recordType' => 'tx_memsy_member',
        'properties' => [
            'disabled' => [
                'fieldName' => 'disable',
            ],
        ],
    ],
];
