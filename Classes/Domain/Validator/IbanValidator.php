<?php

namespace TYPO3Incubator\Memsy\Domain\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class IbanValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'propertyThatNeedsToBeSet' => [null, 'The property that needs to be set for the IBAN to be valid', 'string'],
    ];

    protected function isValid(mixed $value): void
    {
        $iban = strtoupper($value);
        if (!verify_iban($iban)) {
            $this->addError(
                'The entered IBAN is not valid.',
                1471702628,
            );
        }
    }

    public function getPropertyThatNeedsToBeSet(): string
    {
        return $this->options['propertyThatNeedsToBeSet'];
    }
}
