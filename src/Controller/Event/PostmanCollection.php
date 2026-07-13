<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Domain\Event\Query\GetAllEvents;
use App\Entity\Event;
use App\Entity\EventProperty;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostmanCollection extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus
    ) {}

    #[Route('/dashboard/event/postman-collection', name: 'event_postman_collection', methods: ['GET'])]
    public function __invoke(): Response
    {
        /** @var Event[] $events */
        $events = $this->queryBus->query(new GetAllEvents());

        $items = [
            $this->createTokenRequestItem(),
        ];
        foreach ($events as $event) {
            if ($event->isRetired() || $event->isDeleted() || $event->isSystemEvent()) {
                continue;
            }

            $items[] = $this->createPostmanItem($event);
        }

        $collection = [
            'info' => [
                'name' => 'EventSourcerer Registered Events',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $items,
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{jwt_token}}',
                        'type' => 'string',
                    ],
                ],
            ],
            'variable' => [
                [
                    'key' => 'baseUrl',
                    'value' => $this->generateUrl('dashboard', [], 0), // Use dashboard URL as base guess
                    'type' => 'string'
                ],
                [
                    'key' => 'streamId',
                    'value' => 'main',
                    'type' => 'string'
                ],
                [
                    'key' => 'applicationId',
                    'value' => 'YOUR_APPLICATION_ID',
                    'type' => 'string'
                ],
                [
                    'key' => 'applicationSecret',
                    'value' => 'YOUR_APPLICATION_SECRET',
                    'type' => 'string'
                ],
                [
                    'key' => 'jwt_token',
                    'value' => '',
                    'type' => 'string'
                ]
            ]
        ];

        $response = new JsonResponse($collection);
        $response->headers->set('Content-Disposition', 'attachment; filename="event-sourcerer-collection.json"');

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function createTokenRequestItem(): array
    {
        return [
            'name' => 'Get Authentication Token',
            'event' => [
                [
                    'listen' => 'test',
                    'script' => [
                        'exec' => [
                            'var jsonData = pm.response.json();',
                            'pm.collectionVariables.set("jwt_token", jsonData.token);'
                        ],
                        'type' => 'text/javascript'
                    ]
                ]
            ],
            'request' => [
                'auth' => [
                    'type' => 'noauth'
                ],
                'method' => 'POST',
                'header' => [
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json',
                    ],
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode([
                        'id' => '{{applicationId}}',
                        'secret' => '{{applicationSecret}}',
                    ], JSON_PRETTY_PRINT),
                ],
                'url' => [
                    'raw' => '{{baseUrl}}/api/login_check',
                    'host' => ['{{baseUrl}}'],
                    'path' => ['api', 'login_check'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function createPostmanItem(Event $event): array
    {
        $properties = [];
        /** @var EventProperty $property */
        foreach ($event->getProperties() as $property) {
            $typeClass = $property->getTypeClass();
            $example = 'value';
            if (method_exists($typeClass, 'exampleInput')) {
                $example = $typeClass::exampleInput();

                // If it looks like a number, cast it to one for the JSON body
                if (is_numeric($example) && (string)(int)$example === $example) {
                    $example = (int)$example;
                } elseif (is_numeric($example) && (string)(float)$example === $example) {
                    $example = (float)$example;
                }
            }

            $properties[$property->getName()] = $example;
        }

        $body = [
            'stream' => '{{streamId}}',
            'event' => $event->getName(),
            'version' => $event->getVersion(),
            'properties' => $properties,
        ];

        return [
            'name' => $event->getName() . ' (v' . $event->getVersion() . ')',
            'request' => [
                'method' => 'POST',
                'header' => [
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json',
                    ],
                ],
                'body' => [
                    'mode' => 'raw',
                    'raw' => json_encode($body, JSON_PRETTY_PRINT),
                ],
                'url' => [
                    'raw' => '{{baseUrl}}/api/stream_events',
                    'host' => ['{{baseUrl}}'],
                    'path' => ['api', 'stream_events'],
                ],
            ],
        ];
    }
}
