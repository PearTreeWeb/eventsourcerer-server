<?php

namespace App\Domain\Common\Query;

use App\Domain\Common\Model\Query;
use App\Entity\RuntimeError;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @implements Query<Paginator<RuntimeError>>
 */
final readonly class GetRuntimeErrors implements Query
{
    public function __construct(public int $start, public int $perPage) {}
}
