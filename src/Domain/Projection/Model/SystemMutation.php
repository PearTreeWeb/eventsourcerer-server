<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\AuthoredBySystem;
use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;

abstract readonly class SystemMutation implements CanMutate
{
    use AuthoredBySystem;

    public function uniqueIdentifier(): AuthorUniqueIdentifier
    {
        return AuthorUniqueIdentifier::fromString(
            sprintf(
                '%s-%s',
                self::author()->toString(),
                static::label()->toString()
            )
        );
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Base->value);
    }
}
