<?php

namespace Src\Enums;

use Exception;

enum IntervalUnitsEnum: string
{
    case UNIT = 'M';
    case PREFIX = 'PT';
    case MINUTE_STEP = '5';

    /**
     * @throws Exception
     */
    public function toInt(): int
    {
        if ($this !== self::MINUTE_STEP) {
            throw new Exception( "'$this->name' can not be converted to int");
        }

        return (int)$this->value;
    }
}