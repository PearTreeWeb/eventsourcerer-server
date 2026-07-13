<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Widget;

use App\Domain\Widget\Model\WidgetName;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<?WidgetName, string>
 */
final class WidgetNameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): WidgetName
    {
        return WidgetName::fromString($value);
    }
}
