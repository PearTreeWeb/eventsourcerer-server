<?php

declare(strict_types=1);

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiDto\StreamEvent as EventDto;
use App\Domain\Client\Command\UpdateProjections;
use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Client\Service\RecordEvent as RecordEventService;
use App\Domain\Common\Command\LogRuntimeError;
use App\Domain\Common\Command\BroadcastEvent;
use App\Domain\Common\Exception\CannotProcessEvent;
use App\Domain\Common\Model\EventWritingError;
use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Exception\CannotRecordEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPayloadProperties;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventWriterRepository;
use App\Domain\Stream\Model\StreamEventId;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Entity\StreamEventProperty;
use App\Exception\EventPayloadIncomplete;
use App\Infrastructure\BasicSerializer;
use App\Repository\Postgres\PostgresStreamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @implements ProcessorInterface<EventDto, StreamEvent>
 */
final readonly class StreamEventProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private ClockInterface $clock,
        private GenerateUuid $generateUuid,
        #[Autowire(service: PostgresStreamRepository::class)]
        private StreamRepository $streamRepository,
        private RecordEventService $recordEvent,
        private EventWriterRepository $eventWriterRepository,
        private BasicSerializer $serializer,
    ) {}

    /**
     * @param object $data
     *
     * @throws CannotRecordEvent
     * @throws CannotProcessEvent
     * @throws EventPayloadIncomplete
     * @throws \Throwable
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): StreamEvent {
        try {
            $streamId = StreamId::fromString($data->stream);

            if ($streamId->isAllStream()) {
                throw CannotRecordEvent::directlyToAllStream();
            }

            $now = $this->clock->now();
            $stream = $this->stream($streamId, $now);

            if (null !== $data->expectedVersion) {
                $serverExpectedNextVersion = $stream->getCurrentVersion() + 1;

                if ($serverExpectedNextVersion !== $data->expectedVersion) {
                    throw CannotProcessEvent::expectedVersionIsDifferent(
                        $serverExpectedNextVersion,
                        $data->expectedVersion
                    );
                }
            }

            $eventName = EventName::fromString($data->event);
            $version = EventVersion::fromInt($data->version);

            try {
                $event = $this->eventWriterRepository->eventWithNameAndVersion($eventName, $version);
            } catch (\Throwable) {
                throw CannotRecordEvent::becauseTheEventIsNotRegistered($eventName, $version);
            }

            if (null === $event) {
                throw CannotRecordEvent::becauseTheEventIsNotRegistered($eventName, $version);
            }

            $eventId = EventId::fromString($event['id']);
            $eventProperties = $this->eventWriterRepository->eventPropertiesForEventWithId($eventId);
            $eventPayloadProperties = EventPayloadProperties::create();

            foreach ($eventProperties as $eventProperty) {
                if (!isset($data->properties[$eventProperty['name']])) {
                    throw EventPayloadIncomplete::becauseItIsMissingProperty($eventProperty['name']);
                }

                $value = $data->properties[$eventProperty['name']];
                /** @var class-string $propertyType */
                $propertyType = $eventProperty['type_class'];

                $propertyType::validate($value);

                $eventPayloadProperties->add(
                    new EventPayloadProperty(
                        EventPropertyName::fromString($eventProperty['name']),
                        EventPropertyValue::fromString($this->serializer->serialize($value, $propertyType))
                    )
                );
            }

            $streamEventId = StreamEventId::fromUuid($this->generateUuid->random());

            $streamEvent = StreamEvent::create(
                $streamEventId,
                $eventId,
                $streamId,
                $eventName,
                $version,
                new ArrayCollection($eventPayloadProperties->mapInto(StreamEventProperty::class)->all()),
                $stream,
                $now
            );

            $this->mapEventProperties($eventProperties, $streamEvent, $now);
            $stream = $stream->incrementCurrentVersion();

            $this->recordEvent->record($streamEvent, $stream);

            $this->commandBus->dispatch(
                new UpdateProjections(
                    $stream,
                    $eventId,
                    $eventPayloadProperties,
                    $streamEvent,
                )
            );

            if (!$event['system_event']) {
                $this->commandBus->dispatch(new BroadcastEvent($streamEvent));
            }

            return $streamEvent;
        } catch (\Throwable $e) {
            $this->commandBus->dispatch(
                new LogRuntimeError(
                    new EventWritingError(json_decode(json_encode($data), true), $e),
                )
            );

            throw $e;
        }
    }

    private function stream(StreamId $streamId, \DateTimeImmutable $now): Stream
    {
        return $this->streamRepository->find($streamId) ?? Stream::create($streamId, $now);
    }

    /**
     * @param array<string, mixed> $eventProperties
     */
    private function mapEventProperties(array $eventProperties, StreamEvent $event, \DateTimeImmutable $now): void
    {
        foreach ($event->getProperties()->toArray() as $property) {
            /** @var array{
             *     type: string,
             *     type_class: string,
             *     id: string,
             *     name: string,
             *     created_at: string,
             *     updated_at: string,
             *     event_id: string,
             *     contains_personal_data: bool,
             * } $blueprintProperty 
             */
            $blueprintProperty = $eventProperties[$property->getName()];

            /** @var StreamEventProperty $property */
            $property
                ->setType($blueprintProperty['type_class'])
                ->setEventPropertyId(Uuid::fromString($blueprintProperty['id']))
                ->setStreamEvent($event)
                ->setCreatedAt($now);
        }
    }
}
