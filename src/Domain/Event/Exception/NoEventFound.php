<?php

declare(strict_types=1);

namespace App\Domain\Event\Exception;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;

final class NoEventFound extends \RuntimeException
{
    public static function withName(EventName $name): self
    {
        return new self(
            sprintf(
                'No event was found with name "%s"',
                $name
            )
        );
    }

    public static function withId(EventId $id): self
    {
        return new self(
            sprintf(
                'No event was found with ID "%s"',
                $id
            )
        );
    }
}
