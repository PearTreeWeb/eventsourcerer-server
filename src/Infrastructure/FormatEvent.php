<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Entity\StreamEvent;

final readonly class FormatEvent
{
    /**
     * @return array{
     *      allSequence: int,
     *      eventVersion: int,
     *      name: string,
     *      number: int,
     *      payload: array<string, mixed>,
     *      stream: string,
     *      occurred: string,
     *  }
     */
    public static function toArray(StreamEvent $streamEvent): array
    {
        return $streamEvent->toScalarArray();
    }

    public static function toJson(StreamEvent $streamEvent): string
    {
        return json_encode(self::toArray($streamEvent), JSON_THROW_ON_ERROR);
    }
}
