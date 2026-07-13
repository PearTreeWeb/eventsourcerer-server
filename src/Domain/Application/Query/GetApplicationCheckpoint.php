<?php

declare(strict_types=1);

namespace App\Domain\Application\Query;

use App\Domain\Common\Model\Query;
use App\Entity\ApplicationCheckpoint;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

/**
 * @implements Query<ApplicationCheckpoint>
 */
final readonly class GetApplicationCheckpoint implements Query
{
    public function __construct(public ApplicationId $applicationId, public StreamId $streamId) {}
}
