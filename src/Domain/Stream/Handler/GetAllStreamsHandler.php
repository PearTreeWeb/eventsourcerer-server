<?php

declare(strict_types=1);

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Stream\Query\GetAllStreams;
use App\Entity\Stream;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllStreamsHandler
{
    public function __construct(private StreamRepository $streamRepository) {}

    /**
     * @return Paginator<Stream>
     */
    public function __invoke(GetAllStreams $query): \Countable&\IteratorAggregate
    {
        return $this->streamRepository->paginated(0, 10);
    }
}
