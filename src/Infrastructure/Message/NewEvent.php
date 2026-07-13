<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class NewEvent implements Message
{
    public function __construct(
        public string $json,
        public StreamId $streamId,
        public Checkpoint $checkpoint
    ) {}

    public function cloneWithWorkerId(WorkerId $workerId): self
    {
        $json = json_decode($this->json, true);

        $json['workerId'] = $workerId->toString();

        return new self(
            json_encode($json),
            $this->streamId,
            $this->checkpoint,
        );
    }
}
