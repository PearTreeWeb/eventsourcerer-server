<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\UserId;
use App\Entity\User;

interface UserRepository
{
    /**
     * @return list<User>
     */
    public function all(): array;

    public function create(User $user, \DateTimeImmutable $createdAt): User;

    public function update(User $user, \DateTimeImmutable $updatedAt): User;

    public function withIdStrict(UserId $id): User;

    public function withEmailAddressStrict(EmailAddress $emailAddress): User;

    public function hasOnlyOneSuperUser(): bool;

    public function delete(UserId $userId): void;
}
