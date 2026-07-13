<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionName;
use App\Entity\Projection;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @implements Query<Projection>
 */
#[Autoconfigure(autowire: false)]
final readonly class GetProjectionByName implements Query
{
    public function __construct(public ProjectionName $name) {}
}
