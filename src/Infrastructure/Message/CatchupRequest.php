<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class CatchupRequest implements Message
{
    public function __construct(
        public StreamId $streamId,
        public ApplicationId $applicationId,
        public WorkerId $workerId,
        public ?Checkpoint $requestedCheckpoint = null
    ) {}
}
