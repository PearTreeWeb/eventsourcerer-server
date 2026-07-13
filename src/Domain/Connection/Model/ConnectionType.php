<?php

declare(strict_types=1);

namespace App\Domain\Connection\Model;

enum ConnectionType: string
{
    case API ='API';
    case SOCKET = 'SOCKET';
}
