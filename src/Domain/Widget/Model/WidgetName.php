<?php

declare(strict_types=1);

namespace App\Domain\Widget\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class WidgetName implements IsString
{
    use FulfilIsString;
}
