<?php

declare(strict_types=1);

namespace App\Controller\Incoming;

use App\Domain\Stream\Query\GetStream;
use App\Entity\StreamEvent;
use App\Entity\StreamEventProperty;
use App\Infrastructure\QueryBus;
use App\Twig\Filter\Deserializer;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{id}/stream', name: 'stream', methods: ['GET'])]
final class Stream extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly Deserializer $deserializer
    ) {}

    public function __invoke(StreamId $id): StreamedResponse
    {
        return new StreamedJsonResponse([
            'events' => $this->streamEvents($id),
        ]);
    }

    /**
     * @return iterable<array{
     *     event: string,
     *     properties: array<array{
     *         name: string,
     *         type: string,
     *         value: mixed,
     *     }>,
     *     stream: string,
     * }>
     */
    private function streamEvents(StreamId $id): iterable
    {
        $stream = $this->queryBus->query(GetStream::withStreamId($id));

        foreach ($stream?->getEvents()->getIterator() as $event) {
            /** @var StreamEvent $event */
            yield [
                'event'      => $event->getEventName(),
                'properties' => $this->mapEventProperties($event->getProperties()->getIterator()),
                'stream'     => $event->getStreamId(),
            ];
        }
    }

    /**
     * @param iterable<StreamEventProperty> $eventProperties
     *
     * @return array{array{name: string, type: string, value: mixed}}
     */
    private function mapEventProperties(iterable $eventProperties): iterable
    {
        return collect($eventProperties)
            ->map(function (StreamEventProperty $property): array {
                return [
                    'name'  => $property->getName(),
                    'type'  => $property->getType(),
                    'value' => $this->deserializer->deserialize($property),
                ];
            })
            ->all();
    }
}
