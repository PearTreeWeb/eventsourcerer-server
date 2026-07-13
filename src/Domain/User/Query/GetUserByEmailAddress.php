<?php

declare(strict_types=1);

namespace App\Domain\User\Query;

use App\Domain\Common\Model\Query;
use App\Domain\User\Model\EmailAddress;
use App\Entity\User;

/**
 * @implements Query<User>
 */
final readonly class GetUserByEmailAddress implements Query
{
    public function __construct(public EmailAddress $emailAddress) {}
}
