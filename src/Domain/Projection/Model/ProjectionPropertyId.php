<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;

final class ProjectionPropertyId implements IsUuid
{
    use FulfilIsUuid;
}
