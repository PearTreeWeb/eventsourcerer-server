<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

final class ExpectedInteger extends \RuntimeException
{
    public static function butReceived(string $value): self
    {
        return new self(
            sprintf(
                'Expected an integer but received "%s"',
                $value
            )
        );
    }
}
