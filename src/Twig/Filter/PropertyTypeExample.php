<?php

declare(strict_types=1);

namespace App\Twig\Filter;

use Twig\Extension\RuntimeExtensionInterface;

final readonly class PropertyTypeExample implements RuntimeExtensionInterface
{
    public function exampleInput(string $typeClass): string
    {
        if (!class_exists($typeClass)) {
            return '""';
        }

        if (!method_exists($typeClass, 'exampleInput')) {
            return '""';
        }

        return $typeClass::exampleInput();
    }
}
