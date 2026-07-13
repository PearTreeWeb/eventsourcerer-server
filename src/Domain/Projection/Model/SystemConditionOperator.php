<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\AuthoredBySystem;
use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Common\Model\FulfilCanProvidePackageUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Extension\Default\ConditionOperators\ConditionOperator;
use App\Extension\Default\PropertyType\Text;

abstract readonly class SystemConditionOperator implements ConditionOperator
{
    use AuthoredBySystem;
    use FulfilCanProvidePackageUniqueIdentifier;

    public function uniqueIdentifier(): AuthorUniqueIdentifier
    {
        return self::uniquePackageIdentifier(self::author(), static::label());
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Base->value);
    }

    public static function parameters(): array
    {
        return ['Value'];
    }

    public static function parameterPropertyTypes(): array
    {
        return [Text::class];
    }
}
