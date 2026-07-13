<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Client\Service\RecordEvent as RecordEventInterface;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Repository\Postgres\PostgresStreamEventRepository;
use App\Repository\Postgres\PostgresStreamRepository;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class RecordEvent implements RecordEventInterface
{
    public function __construct(
        #[Autowire(service: PostgresStreamRepository::class)]
        private StreamRepository $streamRepository,
        #[Autowire(service: PostgresStreamEventRepository::class)]
        private StreamEventRepository $streamEventRepository
    ) {}

    public function record(StreamEvent $streamEvent, Stream $stream): void
    {
        $this->streamRepository->update($stream);

        $streamEvent = $streamEvent
            ->setSequence($this->streamEventRepository->nextSequence(StreamId::fromString($streamEvent->getStreamId())))
            ->setAllSequence($this->streamEventRepository->nextAllSequence());

        $this->streamEventRepository->create($streamEvent);
        $this->streamRepository->update($stream);
    }
}
