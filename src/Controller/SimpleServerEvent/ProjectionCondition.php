<?php

namespace App\Controller\SimpleServerEvent;

use App\Domain\Projection\Model\ProjectionCondition as ProjectionConditionStatus;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Query\GetProjection;
use App\Domain\Projection\Query\GetProjectionMaxSequence;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/simple-server-events/projection/{id}/condition',
    name: 'simple_server_events_for_projection_condition',
)]
final class ProjectionCondition extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(ProjectionId $id): EventStreamResponse
    {
        return new EventStreamResponse(function () use ($id) {
            $condition = $this->projectionCondition($id);

            yield new ServerEvent(json_encode($condition, JSON_THROW_ON_ERROR), type: 'projectionCondition');

            $sleep = ProjectionConditionStatus::Resetting->value === $condition['condition'] ? 1 : 4;

            sleep($sleep);
        });
    }

    /**
     * @return array{condition: string, currentAllSequence: int, maxAllSequence: int}
     */
    private function projectionCondition(ProjectionId $id): array
    {
        $projection = $this->queryBus->query(GetProjection::withId($id));

        return [
            'condition' => $projection->getCondition(),
            'currentAllSequence' => $projection->getLastAllSequenceCheckpointProcessed(),
            'maxAllSequence' => $this->queryBus->query(new GetProjectionMaxSequence($id)),
        ];
    }
}
