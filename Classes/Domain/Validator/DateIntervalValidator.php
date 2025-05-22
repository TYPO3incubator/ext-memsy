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

/**
 * DateIntervalValidator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class DateIntervalValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'interval' => [null, 'The interval to validate, must be readable by the DateInterval class', 'string'],
        'message' => [null, 'Translation key or message for invalid value', 'string'],
    ];

    protected function isValid(mixed $value): void
    {
        // Early return if value is not a date object
        if (!($value instanceof \DateTime)) {
            return;
        }

        try {
            $interval = new \DateInterval($this->options['interval']);
        } catch (\DateMalformedIntervalStringException) {
            $this->addError(
                'The configured date interval is not valid.',
                1747596596,
            );

            return;
        }

        $now = new \DateTimeImmutable();
        $target = $value->add($interval);

        if ($target->getTimestamp() >= $now->getTimestamp()) {
            $this->addError(
                $this->translateErrorMessage($this->options['message']),
                1747596742,
            );
        }
    }
}
