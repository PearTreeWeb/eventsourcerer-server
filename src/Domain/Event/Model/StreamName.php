<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class StreamName implements IsString
{
    use FulfilIsString;
}
