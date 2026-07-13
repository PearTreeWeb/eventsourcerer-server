<?php

namespace App\Domain\Common\Model;

use App\Domain\Common\HasKey;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface IsCollection
{
    /**
     * @param array<TKey, TValue> $items
     *
     * @return static<TKey, TValue>
     */
    public static function fromArray(array $items): static;

    /**
     * @return self<TKey, TValue>
     */
    public static function create(): self;

    /**
     * @param HasKey&TValue $item
     */
    public function add(HasKey $item): void;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
