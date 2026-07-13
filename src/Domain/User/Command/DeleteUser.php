<?php

namespace App\Domain\User\Command;

use App\Domain\User\Model\UserId;

final readonly class DeleteUser
{
    public function __construct(public UserId $userId) {}
}