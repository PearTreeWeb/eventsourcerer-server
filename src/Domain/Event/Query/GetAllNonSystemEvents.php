<?php

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Event;

/**
 * @implements Query<Event[]>
 */
final readonly class GetAllNonSystemEvents implements Query
{
}
