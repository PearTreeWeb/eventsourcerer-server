<?php

namespace App\Domain\Application\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class Hostname implements IsString
{
    use FulfilIsString;
}
