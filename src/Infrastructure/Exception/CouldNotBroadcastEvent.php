<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

final class CouldNotBroadcastEvent extends \RuntimeException
{
    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
