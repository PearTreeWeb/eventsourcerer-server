<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Event;

use App\Domain\Event\Model\EventId;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<EventId, string>
 */
final class EventIdTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): EventId
    {
        return EventId::fromString($value);
    }
}
