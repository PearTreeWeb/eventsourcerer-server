<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionByName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionByNameHandler
{
    public function __construct(private ProjectionRepository $projectionRepository) {}

    public function __invoke(GetProjectionByName $query): Projection
    {
        return $this->projectionRepository->findByNameStrict($query->name);
    }
}
