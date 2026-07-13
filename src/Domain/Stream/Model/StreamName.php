<?php

declare(strict_types=1);

namespace App\Domain\Stream\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final readonly class StreamName implements IsString
{
    use FulfilIsString;
}
