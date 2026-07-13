<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

final class PropertyTypeValueIsIncompatible extends \RuntimeException
{
    public static function with(mixed $value, string $propertyType): self
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return new self(
            sprintf(
                'Value "%s" is incompatible with property type "%s"',
                $value,
                $propertyType
            )
        );
    }
}
