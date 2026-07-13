<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

final class ExpectedDateTimeImmutable extends \RuntimeException
{
    public static function butReceived(string $value): self
    {
        return new self(
            sprintf(
                'Expected a DateTimeImmutable object but received %s',
                $value
            )
        );
    }
}
