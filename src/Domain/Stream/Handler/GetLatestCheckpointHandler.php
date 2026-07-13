<?php

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Stream\Query\GetLatestCheckpoint;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetLatestCheckpointHandler
{
    public function __construct(private StreamEventRepository $streamEventRepository) {}

    public function __invoke(GetLatestCheckpoint $query): Checkpoint
    {
        return $this->streamEventRepository->maxSequenceFor($query->id);
    }
}
