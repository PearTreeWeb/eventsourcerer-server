<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Service\ProjectionBuilder;
use App\Domain\Projection\Service\ProjectionBuilderFactory;
use Psr\Clock\ClockInterface;

final readonly class DoctrineProjectionBuilderFactory implements ProjectionBuilderFactory
{
    public function __construct(
        private ClockInterface $clock,
        private GenerateUuid $generateUuid,
        private EventRepository $eventRepository
    ) {}

    public function create(ProjectionName $name): ProjectionBuilder
    {
        return DoctrineProjectionBuilder::create(
            $this->clock,
            $this->generateUuid,
            $this->eventRepository,
            $name
        );
    }
}
