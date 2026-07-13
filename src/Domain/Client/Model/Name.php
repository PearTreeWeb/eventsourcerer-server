<?php

declare(strict_types=1);

namespace App\Domain\Client\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class Name implements IsString
{
    use FulfilIsString;
}
