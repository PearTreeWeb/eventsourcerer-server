<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\StreamEvent;
use App\Infrastructure\EventSourcerer\Service\EventFetcher;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProviderInterface<StreamEvent>
 */
final readonly class StreamEventProvider implements ProviderInterface
{
    public function __construct(private EventFetcher $eventFetcher) {}

    /**
     * @param array{request?: Request, resource_class?: string} $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $queryParams = $context['request']->query;

        return $this->eventFetcher->fetchFor(
            ApplicationId::fromString($queryParams->get('applicationId')),
            StreamId::fromString($queryParams->get('streamId'))
        );
    }
}
