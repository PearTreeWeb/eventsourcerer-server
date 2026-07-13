<?php

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\CanProvidePackageUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Projection\Model\ConditionLabel;

interface ConditionOperator extends CanProvidePackageUniqueIdentifier
{
    public static function label(): ConditionLabel;

    public static function author(): Author;
    
    /**
     * @return string[]
     */
    public static function parameters(): array;

    /**
     * @return class-string<\App\Domain\Common\Model\PropertyType>[]
     */
    public static function parameterPropertyTypes(): array;

    public function compute(mixed $value, mixed $parameter): bool;

    public static function package(): Package;
}
