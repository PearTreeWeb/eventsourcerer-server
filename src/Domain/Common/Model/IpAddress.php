<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class IpAddress implements IsString
{
    use FulfilIsString;
}
