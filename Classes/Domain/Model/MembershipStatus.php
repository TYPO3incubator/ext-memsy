<?php

namespace TYPO3Incubator\Memsy\Domain\Model;

enum MembershipStatus: int
{
    case Unconfirmed = -1;
    case Pending  = 0;
    case Active   = 1;
    case Inactive = 2;

    public function label(): string
    {
        return match($this) {
            self::Unconfirmed => 'Unconfirmed',
            self::Pending     => 'Pending',
            self::Active      => 'Active',
            self::Inactive    => 'Inactive',
        };
    }
}
