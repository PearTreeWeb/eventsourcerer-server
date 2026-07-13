<?php

declare(strict_types=1);

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;
use App\Entity\StreamEvent;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

/**
 * @implements Query<Paginator<StreamEvent>>
 */
final readonly class GetStreamEvents implements Query
{
    private function __construct(public StreamId $id, public int $start, public int $limit) {}

    public static function withStreamId(StreamId $id, int $start, int $limit): self
    {
        return new self($id, $start, $limit);
    }
}
