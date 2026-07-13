<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Common\Model\PropertyType;

final readonly class PropertyTypes
{
    /** @param iterable<PropertyType> $propertyTypes */
    public function __construct(private iterable $propertyTypes) {}

    /**
     * @return iterable<PropertyType>
     */
    public function all(): iterable
    {
        return $this->propertyTypes;
    }
}
