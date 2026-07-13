<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Application;

use App\Domain\Application\Model\Hostname;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<Hostname, string>
 */
final class HostnameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string|null $value
     */
    public function reverseTransform(mixed $value): ?Hostname
    {
        if (null === $value) {
            return null;
        }

        return Hostname::fromString($value);
    }
}
