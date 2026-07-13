<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\HasKey;
use App\Domain\Common\Model\PropertyType;

final readonly class EventProperty implements HasKey
{
    public function __construct(
        public EventPropertyId $id,
        public EventPropertyName $name,
        public PropertyType $type,
        public bool $containsPersonalData,
    ) {}

    /**
     * @return array{id: string, name: string, type: string, containsPersonalData: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name->toString(),
            'type' => $this->type::class,
            'containsPersonalData' => $this->containsPersonalData,
        ];
    }

    public function key(): string
    {
        return $this->id->toString();
    }
}
