<?php

declare(strict_types=1);

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Event\Model\EventId;
use App\Entity\Event;

/**
 * @implements Query<Event>
 */
final readonly class GetEvent implements Query
{
    private function __construct(public EventId $id) {}

    public static function withId(EventId $id): self
    {
        return new self($id);
    }
}
