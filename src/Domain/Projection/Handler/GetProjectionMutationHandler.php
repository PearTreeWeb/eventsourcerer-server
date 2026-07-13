<?php

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionMutation;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Entity\ProjectionMutation;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionMutationHandler
{
    public function __construct(private ProjectionMutationRepository $repository) {}

    public function __invoke(GetProjectionMutation $query): ProjectionMutation
    {
        return $this->repository->byIdStrict($query->id);
    }
}