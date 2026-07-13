<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Domain\User\Model\User;

final readonly class UpdateUser
{
    public function __construct(public User $user) {}
}
