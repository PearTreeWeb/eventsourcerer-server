<?php

declare(strict_types=1);

namespace App\Domain\Application\Query;

use App\Domain\Common\Model\Query;
use App\Entity\ApplicationCheckpoint;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

/**
 * @implements Query<iterable<ApplicationCheckpoint>>
 */
final readonly class GetApplicationCheckpoints implements Query
{
    public function __construct(public ApplicationId $id) {}
}
