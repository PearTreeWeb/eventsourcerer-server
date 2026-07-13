<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\Label;
use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;

final class ConditionLabel implements Label
{
    use FulfilIsString;
}
