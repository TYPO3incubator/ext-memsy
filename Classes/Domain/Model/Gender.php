<?php

namespace TYPO3Incubator\Memsy\Domain\Model;

enum Gender: int
{
    case NoSelection  = -1;
    case Other  = 0;
    case Male   = 1;
    case Female = 2;

    public function label(): string
    {
        return match($this) {
            self::NoSelection  => 'No selection',
            self::Other  => 'Other',
            self::Male   => 'Male',
            self::Female => 'Female',
        };
    }
}
