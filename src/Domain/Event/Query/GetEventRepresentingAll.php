<?php

declare(strict_types=1);

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Event;

/**
 * @implements Query<Event>
 */
final readonly class GetEventRepresentingAll implements Query
{
}
