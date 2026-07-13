<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\IsUuid;
use App\Domain\Common\Model\FulfilIsUuid;

final class ProjectionPartitionId implements IsUuid
{
    use FulfilIsUuid;
}
