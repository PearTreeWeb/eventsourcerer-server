<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Entity\User;

final readonly class RegisterUser
{
    public function __construct(public User $user) {}
}
