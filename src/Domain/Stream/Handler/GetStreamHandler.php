<?php

declare(strict_types=1);

namespace App\Domain\Stream\Handler;

use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Stream\Query\GetStream;
use App\Entity\Stream;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetStreamHandler
{
    public function __construct(private StreamRepository $streamRepository) {}

    public function __invoke(GetStream $query): ?Stream
    {
        return $this->streamRepository->withId($query->id);
    }
}
