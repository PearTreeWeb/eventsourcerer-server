<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class EmailAddress implements IsString
{
    use FulfilIsString;
}
