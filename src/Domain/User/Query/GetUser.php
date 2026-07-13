<?php

declare(strict_types=1);

namespace App\Domain\User\Query;

use App\Domain\Common\Model\Query;
use App\Domain\User\Model\UserId;
use App\Entity\User;

/**
 * @implements Query<User>
 */
final readonly class GetUser implements Query
{
    private function __construct(public UserId $id) {}

    public static function withId(UserId $id): self
    {
        return new self($id);
    }
}
