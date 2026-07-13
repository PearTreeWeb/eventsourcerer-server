<?php

declare(strict_types=1);

namespace App\Domain\Stream\Exception;

use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class StreamDoesNotExist extends \RuntimeException
{
    public static function withId(StreamId $id): self
    {
        return new self(
            sprintf('There is no stream with id "%s"', $id)
        );
    }
}
