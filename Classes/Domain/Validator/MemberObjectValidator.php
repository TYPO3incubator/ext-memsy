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

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractGenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * MemberObjectValidator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class MemberObjectValidator extends AbstractGenericObjectValidator
{
    public function validate(mixed $value): Result
    {
        if ($value instanceof AbstractDomainObject) {
            /** @var \SplObjectStorage<ValidatorInterface> $propertyValidators */
            foreach ($this->propertyValidators as $propertyValidators) {
                foreach ($propertyValidators as $propertyValidator) {
                    if ($propertyValidator instanceof PasswordRepeatValidator) {
                        $propertyValidator->setPasswordToCompare(
                            $value->_getProperty($propertyValidator->getPropertyToCompare()),
                        );
                    }
                }
            }
        }

        return parent::validate($value);
    }
}
