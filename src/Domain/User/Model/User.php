<?php

declare(strict_types=1);

namespace App\Domain\User\Model;

final readonly class User
{
    public function __construct(
        public UserId $id,
        public EmailAddress $emailAddress,
        public Role $role
    ) {}
}
