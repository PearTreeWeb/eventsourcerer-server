<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjection;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionHandler
{
    public function __construct(private ProjectionRepository $projectionRepository) {}

    public function __invoke(GetProjection $query): Projection
    {
        return $this->projectionRepository->find($query->id);
    }
}
