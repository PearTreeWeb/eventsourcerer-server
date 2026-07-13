<?php

declare(strict_types=1);

namespace App\Twig\Filter;

use App\Entity\StreamEventProperty;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class Deserializer implements RuntimeExtensionInterface
{
    public function deserialize(StreamEventProperty $value): mixed
    {
        return (string) $value;
    }
}
