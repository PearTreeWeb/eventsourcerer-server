<?php

declare(strict_types=1);

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;

/**
 * @implements Query<\Countable&\IteratorAggregate>
 */
final readonly class GetAllStreamsPaginated implements Query
{
    public function __construct(public int $start, public int $max, public ?string $search = null) {}
}
