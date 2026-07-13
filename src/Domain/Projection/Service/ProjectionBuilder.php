<?php

namespace App\Domain\Projection\Service;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Projection\Model\Mutation;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Model\ProjectionProperty;
use App\Entity\Projection;
use Psr\Clock\ClockInterface;

interface ProjectionBuilder
{
    public static function create(
        ClockInterface $clock,
        GenerateUuid $generateUuid,
        EventRepository $eventRepository,
        ProjectionName $name,
    ): self;

    public function addProperty(ProjectionProperty $property): self;

    public function addMutation(Mutation $mutation): self;

    public function build(): Projection;
}
