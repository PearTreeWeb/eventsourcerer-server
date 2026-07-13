<?php

declare(strict_types=1);

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Stream\Query\GetAllStreamsPaginated;
use App\Entity\Stream;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllStreamsPaginatedHandler
{
    public function __construct(private StreamRepository $streamRepository) {}

    /**
     * @return iterable<Stream>
     */
    public function __invoke(GetAllStreamsPaginated $query): iterable
    {
        return $this->streamRepository->paginated($query->start, $query->max, $query->search);
    }
}
