<?php

declare(strict_types=1);

namespace App\Twig;

use App\Twig\Filter\Deserializer;
use App\Twig\Filter\PropertyTypeExample;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('deserialize', [Deserializer::class, 'deserialize']),
            new TwigFilter('example_input', [PropertyTypeExample::class, 'exampleInput']),
        ];
    }

    public function getFunctions(): array
    {
        return [];
    }
}
