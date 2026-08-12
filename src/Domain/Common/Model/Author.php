<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class Author implements IsString
{
    use FulfilIsString;

    private const string EVENT_SOURCERER = 'EventSourcerer';

    public static function eventSourcerer(): self
    {
        return self::fromString(self::EVENT_SOURCERER);
    }
}
