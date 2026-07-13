<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Application;

use App\Form\DataTransformer\StringTransformer;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<ApplicationId, string>
 */
final class ApplicationIdTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): ApplicationId
    {
        return ApplicationId::fromString($value);
    }
}
