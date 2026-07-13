<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, EventPayloadProperty>
 */
final readonly class EventPayloadProperties implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, EventPayloadProperty> $items
     */
    use FulfilIsCollection;

    /**
     * @return array<string, string>
     */
    public function toScalarArray(): array
    {
        $scalarArray = [];

        foreach ($this->items as $item) {
            /** @var EventPayloadProperty $item */
            $scalarArray[$item->name->toString()] = $item->value->toString();
        }

        return $scalarArray;
    }
}
