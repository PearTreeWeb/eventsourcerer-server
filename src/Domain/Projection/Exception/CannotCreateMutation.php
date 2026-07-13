<?php

declare(strict_types=1);

namespace App\Domain\Projection\Exception;

final class CannotCreateMutation extends \RuntimeException
{
    public static function withoutSettingMutationType(): self
    {
        return new self(
            'Cannot create mutation without setting mutation type first'
        );
    }

    public static function withoutSettingEventPropertyName(): self
    {
        return new self(
            'Cannot create mutation without setting event property name first'
        );
    }

    public static function withMissingProperties(): self
    {
        return new self(
            'Cannot create mutation with missing properties'
        );
    }
}
