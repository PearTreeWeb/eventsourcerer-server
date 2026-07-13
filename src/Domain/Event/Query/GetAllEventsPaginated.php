<?php

declare(strict_types=1);

namespace App\Domain\Event\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Event;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @implements Query<Paginator<Event>>
 */
final readonly class GetAllEventsPaginated implements Query
{
    public function __construct(
        public int $start,
        public int $max,
        public ?string $search = null
    ) {}
}
