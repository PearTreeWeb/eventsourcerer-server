<?php

declare(strict_types=1);

namespace App\Exception;

final class EventPayloadIncomplete extends \RuntimeException
{
    public static function becauseItIsMissingProperty(string $property): self
    {
        return new self(
            sprintf(
                'Event payload is incomplete, property "%s" is missing',
                $property
            )
        );
    }

    public static function becauseItIsMissingStreamId(): self
    {
        return new self('Event payload is incomplete because the stream ID is missing');
    }
}
