<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Projection;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?ProjectionEventPropertyId, string>
 */
final class ProjectionEventPropertyIdTransformer extends StringTransformer implements DataTransformerInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    /**
     * @param null|string $value
     */
    public function reverseTransform(mixed $value): ProjectionEventPropertyId
    {
        if (null === $value) {
            return ProjectionEventPropertyId::fromUuid(
                $this->generateUuid->random()
            );
        }

        return ProjectionEventPropertyId::fromString($value);
    }
}
