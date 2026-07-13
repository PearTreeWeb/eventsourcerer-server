<?php

declare(strict_types=1);

namespace App\Domain\Event\Exception;

use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;

final class CannotRecordEvent extends \RuntimeException
{
    public static function directlyToAllStream(): self
    {
        return new self('Writing an event directly to the all stream is not allowed');
    }

    public static function becauseTheEventIsNotRegistered(EventName $name, EventVersion $eventVersion): self
    {
        return new self(
            sprintf(
                'Could not record event because there is no event registered with the name "%s" and version %d',
                $name,
                $eventVersion->toInt(),
            )
        );
    }
}
