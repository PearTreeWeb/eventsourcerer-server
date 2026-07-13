<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Event;

use App\Domain\Event\Model\EventPropertyId;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<EventPropertyId, ?string>
 */
final class EventPropertyIdTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param null|string $value
     */
    public function reverseTransform(mixed $value): EventPropertyId
    {
        if (null === $value) {
            return EventPropertyId::null();
        }

        return EventPropertyId::fromString($value);
    }
}
