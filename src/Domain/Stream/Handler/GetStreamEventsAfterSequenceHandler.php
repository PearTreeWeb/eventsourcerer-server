<?php

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Stream\Query\GetStreamEventsAfterSequence;
use App\Entity\StreamEvent;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetStreamEventsAfterSequenceHandler
{
    public function __construct(private StreamEventRepository $streamEventRepository) {}

    /**
     * @return Paginator<StreamEvent>
     */
    public function __invoke(GetStreamEventsAfterSequence $query): \Countable&\IteratorAggregate
    {
        return $this
            ->streamEventRepository
            ->withStreamIdPaginated(
                $query->id,
                0,
                $query->limit,
                $query->afterSequence,
                $query->ascending,
            );
    }
}
