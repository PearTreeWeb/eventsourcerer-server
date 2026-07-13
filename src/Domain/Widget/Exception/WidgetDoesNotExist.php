<?php

declare(strict_types=1);

namespace App\Domain\Widget\Exception;

final class WidgetDoesNotExist extends \RuntimeException
{
    public static function withName(string $name): self
    {
        return new self(
            sprintf(
                'Widget does not exist with name "%s"',
                $name
            )
        );
    }
}
