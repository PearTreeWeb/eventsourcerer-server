<?php

declare(strict_types=1);

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Stream;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @implements Query<Paginator<Stream>>
 */
final readonly class GetAllStreams implements Query
{
}
