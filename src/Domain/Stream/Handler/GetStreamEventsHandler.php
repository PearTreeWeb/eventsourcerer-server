<?php

declare(strict_types=1);

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Stream\Query\GetStreamEvents;
use App\Entity\StreamEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetStreamEventsHandler
{
    public function __construct(private StreamEventRepository $streamEventRepository) {}

    /**
     * @return iterable<StreamEvent>
     */
    public function __invoke(GetStreamEvents $query): iterable
    {
        return $this->streamEventRepository->withStreamIdPaginated($query->id, $query->start, $query->limit);
    }
}
