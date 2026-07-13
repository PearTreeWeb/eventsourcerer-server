<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class ReadStream implements Message
{
    public function __construct(
        public ApplicationId $applicationId,
        public StreamId $streamId,
        public ?Checkpoint $start = null,
        public ?Checkpoint $end = null
    ) {}
}
