<?php

declare(strict_types=1);

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Entity\Event;

/**
 * @implements Query<Event>
 */
final readonly class GetEventByName implements Query
{
    public function __construct(public EventName $name, public EventVersion $version) {}
}
