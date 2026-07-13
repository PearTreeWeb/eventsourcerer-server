<?php

declare(strict_types=1);

namespace App\Tests\Double\Repository;

use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\UserId;
use App\Domain\User\Repository\UserRepository as UserRepositoryInterface;
use App\Entity\User;
use App\Tests\Double\Entity;
use App\Tests\Double\Id;

final class UserRepository implements UserRepositoryInterface
{
    public function create(User $user, \DateTimeImmutable $createdAt): User
    {
        return $user;
    }

    public function all(): array
    {
        return [Entity::user()];
    }

    public function update(User $user, \DateTimeImmutable $updatedAt): User
    {
        return $user;
    }

    public function withIdStrict(UserId $id): User
    {
        return Entity::user($id);
    }

    public function hasOnlyOneSuperUser(): bool
    {
        return true;
    }

    public function withEmailAddressStrict(EmailAddress $emailAddress): User
    {
        return Entity::user(Id::userId());
    }

    public function delete(UserId $userId): void
    {
    }
}
