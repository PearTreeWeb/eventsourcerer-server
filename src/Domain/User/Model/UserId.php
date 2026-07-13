<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;

final class UserId implements IsUuid
{
    use FulfilIsUuid;
}
