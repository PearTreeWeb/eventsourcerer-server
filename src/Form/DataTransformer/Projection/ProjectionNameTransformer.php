<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Projection;

use App\Domain\Projection\Model\ProjectionName;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<ProjectionName, string>
 */
final class ProjectionNameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): ProjectionName
    {
        return ProjectionName::fromString($value);
    }
}
