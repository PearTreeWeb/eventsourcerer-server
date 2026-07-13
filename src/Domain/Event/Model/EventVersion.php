<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsInteger;
use PearTreeWeb\EventSourcerer\Common\Model\IsInteger;

final class EventVersion implements IsInteger
{
    public const int DEFAULT = 1;

    use FulfilIsInteger;
}


