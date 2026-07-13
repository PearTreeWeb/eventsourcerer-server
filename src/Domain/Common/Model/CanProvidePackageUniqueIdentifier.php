<?php

namespace App\Domain\Common\Model;

interface CanProvidePackageUniqueIdentifier
{
    public function uniqueIdentifier(): AuthorUniqueIdentifier;
}
