<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionPropertyMutations;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Entity\ProjectionMutation;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionPropertyMutationsHandler
{
    public function __construct(private ProjectionMutationRepository $projectionMutationRepository) {}

    /**
     * @return ProjectionMutation[]
     */
    public function __invoke(GetProjectionPropertyMutations $query): array
    {
        return $this->projectionMutationRepository->findAllWithEventIdAndEventPropertyId(
            $query->eventId,
            $query->id
        );
    }
}
