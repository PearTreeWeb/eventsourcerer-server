<?php

declare(strict_types=1);

namespace App\Infrastructure;

interface Serializer
{
    public function serialize(mixed $data, string $type): string;

    public function deserialize(mixed $data, string $type): mixed;
}
