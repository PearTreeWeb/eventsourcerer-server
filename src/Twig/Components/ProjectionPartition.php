<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Entity\ProjectionState;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProjectionPartition
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $streamId = '';

    #[LiveProp(writable: false)]
    public string $projectionId = '';

    public function __construct(private readonly ProjectionStateRepository $repository) {}

    public function getProjectionState(): ?ProjectionState
    {
        if ('' === $this->streamId) {
            return null;
        }

        return $this->repository->findByStreamAndProjectionId(
            StreamId::fromString($this->streamId),
            ProjectionStateType::Main,
            ProjectionId::fromString($this->projectionId),
        );
    }
}
