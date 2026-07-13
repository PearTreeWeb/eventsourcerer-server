<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Tool\Repository\NonEncryptedStreamEvents;

final readonly class PostgresNonEncryptedStreamEvents implements NonEncryptedStreamEvents
{
    /**
     * @return iterable<mixed>
     */
    public function writtenBefore(\DateTimeImmutable $date): iterable
    {
        // TODO: Implement writtenBefore() method.
        return [];
    }
}
