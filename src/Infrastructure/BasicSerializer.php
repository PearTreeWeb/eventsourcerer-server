<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Model\PropertyType;

final readonly class BasicSerializer implements Serializer
{
    /**
     * @param class-string<PropertyType> $type
     */
    public function serialize(mixed $data, string $type): string
    {
        return $type::serialize($data);
    }

    /**
     * @param class-string<PropertyType> $type
     */
    public function deserialize(mixed $data, string $type): mixed
    {
        return $type::deserialize($data);
    }
}
