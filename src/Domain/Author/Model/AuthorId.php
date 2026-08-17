<?php

declare(strict_types=1);

namespace App\Domain\Author\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;

final class AuthorId implements IsUuid
{
    use FulfilIsUuid;
}
