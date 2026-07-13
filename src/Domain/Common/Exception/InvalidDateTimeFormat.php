<?php

namespace App\Domain\Common\Exception;

final class InvalidDateTimeFormat extends \RuntimeException
{
    public static function with(string $value): self
    {
        return new self(sprintf('Invalid date time format: %s', $value));
    }
}