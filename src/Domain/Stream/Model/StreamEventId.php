<?php

declare(strict_types=1);

namespace App\Domain\Stream\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;

final class StreamEventId implements IsUuid
{
    use FulfilIsUuid;
}
