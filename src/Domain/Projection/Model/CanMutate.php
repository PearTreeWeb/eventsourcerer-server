<?php

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\CanProvidePackageUniqueIdentifier;
use App\Domain\Common\Model\HasAuthor;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;

interface CanMutate extends CanProvidePackageUniqueIdentifier, HasAuthor
{
    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): mixed;

    public function preposition(): MutationPreposition;

    public static function label(): MutationLabel;

    public static function package(): Package;

    /**
     * @return MutationDisplayPart[]
     */
    public static function displayOrder(): array;

    public function compatibleWith(PropertyType $propertyType): bool;
}
