<?php

declare(strict_types=1);

namespace App\Domain\Application\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Application;

/**
 * @implements Query<iterable<Application>>
 */
final readonly class GetAllApplications implements Query
{
}
