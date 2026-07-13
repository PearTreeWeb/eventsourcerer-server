<?php

declare(strict_types=1);

namespace App\ApiDto;

final class StreamEvent
{
    public string $stream;
    public string $event;
    /**
     * @var array<string, mixed>
     */
    public array $properties;
    public int $version;
    public ?int $expectedVersion = null;

    /**
     * @return array{
     *     stream: string,
     *     event: string,
     *     properties: array<string, mixed>,
     *     version: int,
     *     expectedVersion: int,
     * }
     */
    public function toArray(): array
    {
        return [
            'stream' => $this->stream,
            'event' => $this->event,
            'properties' => $this->properties,
            'version' => $this->version,
            'expectedVersion' => $this->expectedVersion,
        ];
    }
}
