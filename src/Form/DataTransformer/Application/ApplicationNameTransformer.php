<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Application;

use App\Domain\Application\Model\ApplicationName;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<ApplicationName, string>
 */
final class ApplicationNameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): ApplicationName
    {
        return ApplicationName::fromString($value);
    }
}
