<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, EventProperty>
 */
final class EventProperties implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, EventProperty> $items
     */
    use FulfilIsCollection;

    /**
     * @return array<array{id: string, name: string, type: string, containsPersonalData: bool}>
     */
    public function asArray(): array
    {
        return $this->items->map(
            fn (EventProperty $property): array => $property->toArray()
        )->all();
    }

    public function findByName(EventPropertyName $name): EventProperty
    {
        return $this
            ->items
            ->first(
                static fn (EventProperty $property) => $property->name->sameAs($name)
            );
    }
}
