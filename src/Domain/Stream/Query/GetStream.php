<?php

declare(strict_types=1);

namespace App\Domain\Stream\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Stream;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

/**
 * @implements Query<?Stream>
 */
final readonly class GetStream implements Query
{
    private function __construct(public StreamId $id) {}

    public static function withStreamId(StreamId $id): self
    {
        return new self($id);
    }
}
