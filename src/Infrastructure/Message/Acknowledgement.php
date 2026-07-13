<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class Acknowledgement implements Message
{
    public function __construct(
        public StreamId $streamId,
        public StreamId $catchupStreamId,
        public ApplicationId $applicationId,
        public WorkerId $workerId,
        public Checkpoint $checkpoint,
        public Checkpoint $allStreamCheckpoint
    ) {}
}
