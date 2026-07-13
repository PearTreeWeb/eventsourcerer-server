<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\PropertyType;

final readonly class ProjectionProperty
{
    public function __construct(
        public ProjectionPropertyName $name,
        public PropertyType $propertyType
    ) {}
}
