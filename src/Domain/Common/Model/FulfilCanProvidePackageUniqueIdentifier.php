<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

trait FulfilCanProvidePackageUniqueIdentifier
{
    protected static function uniquePackageIdentifier(Author $author, Label $label): AuthorUniqueIdentifier
    {
        return AuthorUniqueIdentifier::fromString(
            sprintf('%s-%s', $author, $label)
        );
    }
}
