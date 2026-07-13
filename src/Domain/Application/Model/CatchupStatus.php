<?php

declare(strict_types=1);

namespace App\Domain\Application\Model;

enum CatchupStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Stale = 'stale';
}
