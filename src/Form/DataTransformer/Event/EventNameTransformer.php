<?php

declare(strict_types=1);

namespace App\Form\DataTransformer\Event;

use App\Domain\Event\Model\EventName;
use App\Form\DataTransformer\StringTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<EventName, string>
 */
final class EventNameTransformer extends StringTransformer implements DataTransformerInterface
{
    /**
     * @param string $value
     */
    public function reverseTransform(mixed $value): EventName
    {
        return EventName::fromString($value);
    }
}
