<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Projection;

use App\Domain\Projection\Model\ProjectionId;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?ProjectionId, string>
 */
final readonly class ProjectionIdTransformer implements DataTransformerInterface
{
    /**
     * @param null|ProjectionId $value
     */
    public function transform(mixed $value): string
    {
        return $value?->toString() ?? '';
    }

    public function reverseTransform(mixed $value): ?ProjectionId
    {
        return $value
            ? ProjectionId::fromString($value)
            : null;
    }
}
