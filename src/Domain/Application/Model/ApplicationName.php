<?php

declare(strict_types=1);

namespace App\Domain\Application\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class ApplicationName implements IsString
{
    use FulfilIsString;
}
