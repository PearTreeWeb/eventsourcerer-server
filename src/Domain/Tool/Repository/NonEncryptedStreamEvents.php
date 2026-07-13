<?php

declare(strict_types=1);

namespace App\Domain\Tool\Repository;

interface NonEncryptedStreamEvents
{
    /**
     * @return iterable<mixed>
     */
    public function writtenBefore(\DateTimeImmutable $date): iterable;
}
