<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class MutationType implements IsString
{
    use FulfilIsString;
}
