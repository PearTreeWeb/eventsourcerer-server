<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

final class ExpectedUuid extends \RuntimeException
{
    public static function butReceived(string $value): self
    {
        return new self(
            sprintf(
                'Expected a UUID value but received %s',
                $value
            )
        );
    }
}
