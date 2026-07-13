<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, ProjectionEventProperty>
 */
final class ProjectionEventProperties implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, ProjectionEventProperty> $items
     */
    use FulfilIsCollection;

    /**
     * @return array<array{
     *     id: string,
     *     name: string,
     *     type: string,
     * }>
     */
    public function toFormArray(): array
    {
        /** @var array<array{
         *     id: string,
         *     name: string,
         *     type: string,
         * }>
         */
        return $this->items->map(
            fn (ProjectionEventProperty $property): array => $property->toArray()
        )->all();
    }

    public function find(string $key): ?ProjectionEventProperty
    {
        return $this->items->first(
            static fn (ProjectionEventProperty $property) => $property->id->toString() === $key
        );
    }
}
