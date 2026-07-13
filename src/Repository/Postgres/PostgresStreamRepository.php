<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Stream\Exception\StreamDoesNotExist;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use Doctrine\DBAL\Connection;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class PostgresStreamRepository implements StreamRepository
{
    public function __construct(
        private Connection $connection,
        /** @phpstan-ignore property.onlyWritten */
        private PostgresStreamEventRepository $streamEventRepository,
    ) {}

    public function update(Stream $stream): Stream
    {
        $this->connection->executeStatement(
            <<<SQL
                INSERT INTO stream (
                    id,
                    current_version,
                    created_at,
                    updated_at
                ) VALUES (
                    :id,
                    :currentVersion,
                    :createdAt,
                    :updatedAt
                )
                ON CONFLICT (id) DO UPDATE
                SET
                    current_version = EXCLUDED.current_version,
                    updated_at = EXCLUDED.updated_at
            SQL,
            [
                'id' => $stream->getId()->toString(),
                'currentVersion' => $stream->getCurrentVersion(),
                'createdAt' => $stream->createdAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $stream->updatedAt()->format('Y-m-d H:i:s'),
            ]
        );


        return $stream;
    }

    public function addEvent(Stream $stream, StreamEvent $event): void
    {
        // Use PostgresStreamEventRepository instead
        throw new \BadMethodCallException('Use PostgresStreamEventRepository::create instead.');
    }

    public function paginated(int $start, int $end, ?string $search = null): \Countable&\IteratorAggregate
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function withId(StreamId $id): ?Stream
    {
        return $this->find($id);
    }

    public function withIdStrict(StreamId $id): Stream
    {
        return $this->find($id) ?? throw StreamDoesNotExist::withId($id);
    }

    public function find(StreamId $id): ?Stream
    {
        $row = $this->connection->fetchAssociative(
            <<<'SQL'
                SELECT
                    id,
                    current_version,
                    created_at,
                    updated_at
                FROM stream
                WHERE id = :id
            SQL,
            [
                'id' => $id->toString(),
            ]
        );

        if (false === $row) {
            return null;
        }

        return Stream::fromDatabase(
            StreamId::fromString((string) $row['id']),
            (int) $row['current_version'],
            new \DateTimeImmutable((string) $row['created_at']),
            new \DateTimeImmutable((string) $row['updated_at']),
        );
    }
}
