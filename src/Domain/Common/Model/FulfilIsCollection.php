<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

use App\Domain\Common\HasKey;
use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TValue
 */
trait FulfilIsCollection
{
    /** @param Collection<TKey, TValue> $items */
    public function __construct(protected readonly Collection $items) {}

    /**
     * @param array<TValue> $items
     *
     * @return static
     */
    public static function fromArray(array $items): static
    {
        $collection = new static(collect());

        foreach ($items as $item) {
            $collection->add($item);
        }

        return $collection;
    }

    public static function create(): self
    {
        return new self(new Collection());
    }

    public function add(HasKey $item): void
    {
        $this->items->put($item->key(), $item);
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items->all();
    }

    public function get(HasKey $key): mixed
    {
        return $this->items->get($key->key());
    }

    public function filter(callable $fn): self
    {
        return self::fromArray($this->items->filter($fn)->all());
    }

    public function each(callable $fn): self
    {
        return self::fromArray($this->items->each($fn)->all());
    }

    /**
     * @template TClass
     *
     * @param class-string<TClass> $class
     *
     * @return Collection<TKey, TClass>
     */
    public function mapInto(string $class): Collection
    {
        return $this->items->map(static fn ($item) => $class::create($item));
    }

    public function map(callable $fn): self
    {
        return self::fromArray($this->items->map($fn)->all());
    }

    /**
     * @return Collection<TKey, TValue>
     */
    public function items(): Collection
    {
        return $this->items;
    }
}
