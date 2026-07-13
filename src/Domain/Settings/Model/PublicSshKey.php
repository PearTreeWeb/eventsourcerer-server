<?php

namespace App\Domain\Settings\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class PublicSshKey implements IsString
{
    use FulfilIsString;
}
