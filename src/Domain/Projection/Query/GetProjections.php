<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Projection;

/**
 * @implements Query<Projection[]>
 */
final readonly class GetProjections implements Query
{
    public function __construct() {}
}
