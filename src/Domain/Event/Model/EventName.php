<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class EventName implements IsString
{
    use FulfilIsString;

    private const string ANY_REPRESENTATION = 'any';

    public static function any(): self
    {
        return new self(self::ANY_REPRESENTATION);
    }
}
