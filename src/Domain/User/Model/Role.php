<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

enum Role: string
{
    case OBSERVER = 'ROLE_OBSERVER';
    case SUPER_USER = 'ROLE_SUPER_USER';
    case USER = 'ROLE_USER';
}
