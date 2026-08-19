<?php

declare(strict_types=1);

namespace App\ApiDto;

final class Event
{
    public string $event;
    public ?string $author = null;
    public int $version = 1;
    /**
     * @var array<string, array{type: string}>
     */
    public array $properties = [];
    public ?string $id = null;
}
