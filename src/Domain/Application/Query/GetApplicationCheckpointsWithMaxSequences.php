<?php

namespace App\Domain\Application\Query;

use App\Domain\Common\Model\Query;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

/**
 * @implements Query<iterable<array{streamId: string, maxSequence: int}>>
 */
final readonly class GetApplicationCheckpointsWithMaxSequences implements Query
{
    public function __construct(public ApplicationId $applicationId) {}
}
