<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjections;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionsHandler
{
    public function __construct(private ProjectionRepository $projectionRepository) {}

    /**
     * @return Projection[]
     */
    public function __invoke(GetProjections $query): array
    {
        return $this->projectionRepository->all();
    }
}
