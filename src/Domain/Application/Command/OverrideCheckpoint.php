<?php

declare(strict_types=1);

namespace App\Domain\Application\Command;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class OverrideCheckpoint
{
    private function __construct(
        public ApplicationId $applicationId,
        public StreamId $streamId,
        public Checkpoint $checkpoint
    ) {}

    public static function for(ApplicationId $applicationId, StreamId $streamId, Checkpoint $checkpoint): self
    {
        return new self($applicationId, $streamId, $checkpoint);
    }
}
