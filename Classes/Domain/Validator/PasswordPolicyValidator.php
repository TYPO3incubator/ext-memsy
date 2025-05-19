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

namespace TYPO3Incubator\Memsy\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3Incubator\Memsy\PasswordPolicy\MemberPasswordPolicyResolver;

/**
 * PasswordPolicyValidator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class PasswordPolicyValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'tableName' => ['fe_users', 'The table name contains the password field', 'string'],
        'fieldName' => ['password', 'The field name that represents a password', 'string'],
    ];

    public function __construct(
        private readonly MemberPasswordPolicyResolver $passwordPolicyResolver,
    ) {}

    protected function isValid(mixed $value): void
    {
        // Early return if invalid password is given
        if (!is_string($value)) {
            return;
        }

        $validator = $this->passwordPolicyResolver->getValidator();

        if ($validator !== null && !$validator->isValidPassword($value)) {
            $messageComponents = [
                'The given password is not strong enough:',
                ...array_map(
                    static fn (string $message) => '- ' . $message,
                    $validator->getValidationErrors(),
                ),
            ];

            $this->addError(
                implode(PHP_EOL, $messageComponents),
                1747594256,
            );
        }
    }
}
