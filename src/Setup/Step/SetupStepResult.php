<?php

declare(strict_types=1);

namespace App\Setup\Step;

final readonly class SetupStepResult
{
    private function __construct(
        public bool $success,
        public string $message,
    ) {}

    public static function success(string $message): self
    {
        return new self(true, $message);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
}
