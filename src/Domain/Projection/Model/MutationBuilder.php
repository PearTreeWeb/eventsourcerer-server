<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Projection\Exception\CannotCreateMutation;

final readonly class MutationBuilder
{
    private function __construct(
        public EventName $eventName,
        public EventVersion $eventVersion,
        public ?CanMutate $mutationType = null,
        public ?EventPropertyName $eventPropertyName = null,
        public ?ProjectionPropertyName $projectionPropertyName = null,
        public ?bool $complete = false
    ) {}

    public static function when(EventName $eventName, EventVersion $version): self
    {
        return new self($eventName, $version);
    }

    public function then(CanMutate $mutationType): self
    {
        return new self($this->eventName, $this->eventVersion, $mutationType);
    }

    public function using(EventPropertyName $eventPropertyName): self
    {
        if (null === $this->mutationType) {
            throw CannotCreateMutation::withoutSettingMutationType();
        }

        return new self(
            $this->eventName,
            $this->eventVersion,
            $this->mutationType,
            $eventPropertyName
        );
    }

    public function update(ProjectionPropertyName $projectionPropertyName): self
    {
        if (null === $this->eventPropertyName) {
            throw CannotCreateMutation::withoutSettingEventPropertyName();
        }

        return new self(
            $this->eventName,
            $this->eventVersion,
            $this->mutationType,
            $this->eventPropertyName,
            $projectionPropertyName,
            true
        );
    }

    public function build(): Mutation
    {
        if (!$this->complete) {
            throw CannotCreateMutation::withMissingProperties();
        }

        return new Mutation(
            $this->eventName,
            $this->eventVersion,
            $this->mutationType,
            $this->eventPropertyName,
            $this->projectionPropertyName
        );
    }
}
