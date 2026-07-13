<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class EventPropertyName implements IsString
{
    use FulfilIsString;

    private const string METADATA = 'Metadata';

    public static function metadata(): self
    {
        return self::fromString(self::METADATA);
    }
}
