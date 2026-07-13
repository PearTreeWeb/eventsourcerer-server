<?php

declare(strict_types=1);

namespace App\Infrastructure;

enum CatchupStatus
{
    case Paused;
    case Running;
    case Stopped;
}
