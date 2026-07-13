<?php

namespace App\Domain\Common\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class ErrorMessage implements IsString
{
    use FulfilIsString;
}
