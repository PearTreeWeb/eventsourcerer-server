<?php

declare(strict_types=1);

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Event\Model\EventId;
use App\Entity\Event;

/**
 * @implements Query<Event[]>
 */
final readonly class GetEventsWithIds implements Query
{
    /**
     * @param EventId[] $ids
     */
    public function __construct(public array $ids) {}
}
