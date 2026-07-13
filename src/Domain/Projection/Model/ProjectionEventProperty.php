<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyName;

final readonly class ProjectionEventProperty implements HasKey
{
    public function __construct(
        public ProjectionEventPropertyId $id,
        public EventPropertyName $name,
        public PropertyType $type
    ) {}

    /**
     * @return array{id: string, name: string, type: string}
     */
    public function toArray(): array
    {
        return [
            'id'   => $this->id->toString(),
            'name' => $this->name->toString(),
            'type' => $this->type::class,
        ];
    }

    public function key(): string
    {
        return $this->id->toString();
    }
}
