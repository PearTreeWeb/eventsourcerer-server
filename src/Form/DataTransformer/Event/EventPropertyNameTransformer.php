<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Event;

use App\Domain\Event\Model\EventPropertyName;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<EventPropertyName, string>
 */
final class EventPropertyNameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): EventPropertyName
    {
        return EventPropertyName::fromString($value);
    }
}
