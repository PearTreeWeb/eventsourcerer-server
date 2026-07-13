<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Entity\ProjectionState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class PostgresProjectionStateRepository implements ProjectionStateRepository
{
    public function __construct(private Connection $connection)  {}

    public function add(ProjectionState $projectionState): void
    {
        $this->connection->executeStatement(
            <<<SQL
                INSERT INTO projection_state (
                    projection_id, stream_id, type, current_version, current_state, created_at, updated_at
                ) VALUES (
                    :projectionId, :streamId, :type, :currentVersion, :currentState, :createdAt, :updatedAt
                )
            SQL,
            [
                'projectionId'   => $projectionState->getProjectionId()->toRfc4122(),
                'streamId'       => $projectionState->getStreamId(),
                'type'           => $projectionState->getType(),
                'currentVersion' => $projectionState->getCurrentVersion(),
                'currentState'   => json_encode($projectionState->getCurrentState()),
                'createdAt'      => $projectionState->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt'      => $projectionState->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            [
                'currentVersion' => ParameterType::BOOLEAN,
            ]
        );

        $id = (int) $this->connection->lastInsertId();

        $ref = new \ReflectionClass($projectionState);
        $idProp = $ref->getProperty('id');
        $idProp->setValue($projectionState, $id);
    }

    public function update(ProjectionState $projectionState): void
    {
        $ref    = new \ReflectionClass($projectionState);
        $idProp = $ref->getProperty('id');

        if (!$idProp->isInitialized($projectionState)) {
            $this->add($projectionState);

            return;
        }

        $this->connection->executeStatement(
            <<<SQL
                UPDATE projection_state SET
                    stream_id       = :streamId,
                    type            = :type,
                    current_version = :currentVersion,
                    current_state   = :currentState,
                    updated_at      = :updatedAt
                WHERE id = :id
            SQL,
            [
                'id'             => $projectionState->getId(),
                'streamId'       => $projectionState->getStreamId(),
                'type'           => $projectionState->getType(),
                'currentVersion' => $projectionState->getCurrentVersion(),
                'currentState'   => json_encode($projectionState->getCurrentState()),
                'updatedAt'      => $projectionState->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            [
                'currentVersion' => ParameterType::BOOLEAN,
            ]
        );
    }

    public function findByStreamAndProjectionId(
        StreamId $streamId,
        ProjectionStateType $type,
        ProjectionId $projectionId
    ): ?ProjectionState {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection_state WHERE stream_id = :streamId AND type = :type AND projection_id = :projectionId',
            [
                'streamId'     => $streamId->toString(),
                'type'         => $type->value,
                'projectionId' => $projectionId->toString(),
            ]
        );

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function findMasterByProjectionId(ProjectionId $projectionId): ?ProjectionState
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection_state WHERE stream_id IS NULL AND projection_id = :projectionId AND type = :type',
            [
                'projectionId' => $projectionId->toString(),
                'type'         => ProjectionStateType::Main->value,
            ]
        );

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function findResetByProjectionId(ProjectionId $projectionId): ?ProjectionState
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection_state WHERE stream_id IS NULL AND projection_id = :projectionId AND type = :type',
            [
                'projectionId' => $projectionId->toString(),
                'type'         => ProjectionStateType::Reset->value,
            ]
        );

        return $row !== false ? $this->hydrate($row) : null;
    }

    public function findAllCurrentWithProjectionId(ProjectionId $projectionId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_state WHERE projection_id = :projectionId AND type = :type',
            [
                'projectionId' => $projectionId->toString(),
                'type'         => ProjectionStateType::Main->value,
            ]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function delete(ProjectionState $projectionState): void
    {
        $this->connection->executeStatement(
            'DELETE FROM projection_state WHERE id = :id',
            ['id' => $projectionState->getId()]
        );
    }

    public function findAllResetStatesWithProjectionId(ProjectionId $projectionId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_state WHERE projection_id = :projectionId AND type = :type',
            [
                'projectionId' => $projectionId->toString(),
                'type'         => ProjectionStateType::Reset->value,
            ]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ProjectionState
    {
        $state = ProjectionState::create(
            ProjectionId::fromString($row['projection_id']),
            ProjectionStateType::from($row['type']),
            new \DateTimeImmutable($row['created_at']),
            isset($row['stream_id']) ? StreamId::fromString($row['stream_id']) : null,
        );

        $state->setCurrentState(
            is_string($row['current_state']) ? json_decode($row['current_state'], true) : ($row['current_state'] ?? [])
        );

        $ref = new \ReflectionClass($state);

        $idProp = $ref->getProperty('id');
        $idProp->setValue($state, (int) $row['id']);

        $updatedAtProp = $ref->getProperty('updatedAt');
        $updatedAtProp->setValue($state, new \DateTimeImmutable($row['updated_at']));

        return $state;
    }
}
