<?php

declare(strict_types=1);

namespace App\Domain\Application\Exception;

final class ApplicationDoesNotExist extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf(
                'Application with ID "%s" does not exist',
                $id
            )
        );
    }
}
