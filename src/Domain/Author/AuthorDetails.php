<?php

declare(strict_types=1);

namespace App\Domain\Author;

use App\Domain\Common\Model\Author;

interface AuthorDetails
{
    public static function author(): Author;
}
