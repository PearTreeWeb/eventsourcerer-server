<?php

declare(strict_types=1);

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiDto\Event as EventDto;
use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Command\RegisterEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Repository\PropertyTypes;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<EventDto, EventDto>
 */
final readonly class EventProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private GenerateUuid $generateUuid,
        private PropertyTypes $propertyTypes,
        private AuthorRepository $authorRepository,
    ) {}

    /**
     * @param EventDto $data
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): EventDto {
        $eventId = EventId::fromUuid($this->generateUuid->random());

        $properties = [];
        foreach ($data->properties as $propertyName => $propertyDefinition) {
            $typeClass = $this->resolvePropertyType($propertyDefinition['type']);

            $properties[] = new EventProperty(
                EventPropertyId::fromUuid($this->generateUuid->random()),
                EventPropertyName::fromString((string) $propertyName),
                $typeClass::create(),
                false,
            );
        }

        $authorId = null;
        if (null !== $data->author) {
            $author = $this->authorRepository->findByName($data->author);
            $authorId = $author?->getId();
        }

        $this->commandBus->dispatch(
            new RegisterEvent(
                $eventId,
                EventName::fromString($data->event),
                EventProperties::fromArray($properties),
                0,
                $authorId,
            )
        );

        $data->id = $eventId->toString();

        return $data;
    }

    /**
     * @return class-string<PropertyType>
     */
    private function resolvePropertyType(string $typeKey): string
    {
        foreach ($this->propertyTypes->all() as $propertyType) {
            $key = $propertyType::package()->toString() . '.' . $propertyType::name()->toString();

            if ($key === $typeKey) {
                return $propertyType::class;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown property type "%s".', $typeKey));
    }
}
