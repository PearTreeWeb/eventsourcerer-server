<?php

declare(strict_types=1);

namespace App\Domain\User\Query;

use App\Domain\Common\Model\Query;
use App\Entity\User;

/**
 * @implements Query<User[]>
 */
final readonly class GetAllUsers implements Query
{
}
