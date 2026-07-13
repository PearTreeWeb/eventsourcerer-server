<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Model\Query;

interface QueryBus
{
    /**
     * @template T
     *
     * @param Query<T> $query
     *
     * @return T
     */
    public function query(Query $query);
}
