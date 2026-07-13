<?php

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

/**
 * @implements Query<Checkpoint>
 */
final readonly class GetLatestCheckpoint implements Query
{
    private function __construct(public StreamId $id)
    {
    }

    public static function forStreamWithId(StreamId $id): self
    {
        return new self($id);
    }
}
