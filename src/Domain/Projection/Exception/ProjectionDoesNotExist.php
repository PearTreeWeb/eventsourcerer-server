<?php

declare(strict_types=1);

namespace App\Domain\Projection\Exception;

final class ProjectionDoesNotExist extends \RuntimeException
{
    public static function withName(string $name): self
    {
        return new self(
            sprintf(
                'Projection with name "%s" does not exist',
                $name
            )
        );
    }
}
