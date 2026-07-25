<?php

namespace App\Extension\Default\PropertyType;

use App\Domain\Common\Model\PropertyType;

trait PropertyTypeComparison
{
    public function isSameTypeAs(PropertyType $propertyType): bool
    {
        return $propertyType::class === $this::class;
    }

    public function canBeUsedWithProjectionPropertyType(PropertyType $propertyType): bool
    {
        if ($this->isSameTypeAs(Collection::create())) {
            return true;
        }

        return $this->isSameTypeAs($propertyType);
    }
}
