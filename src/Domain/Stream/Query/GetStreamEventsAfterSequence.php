<?php

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;
use App\Entity\StreamEvent;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

/**
 * @implements Query<Paginator<StreamEvent>>
 */
final readonly class GetStreamEventsAfterSequence implements Query
{
    private function __construct(
        public StreamId $id,
        public int $afterSequence,
        public int $limit,
        public bool $ascending
    ) {}

    public static function withStreamId(StreamId $id, int $afterSequence, int $limit, bool $ascending = false): self
    {
        return new self($id, $afterSequence, $limit, $ascending);
    }
}
