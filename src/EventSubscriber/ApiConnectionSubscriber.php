<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Domain\Common\Model\IpAddress;
use App\Domain\Connection\Model\ConnectionType;
use App\Domain\Connection\Service\RecordConnection;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ApiConnectionSubscriber implements EventSubscriberInterface
{
    public function __construct(private RecordConnection $recordConnection) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['registerConnection', EventPriorities::PRE_RESPOND],
        ];
    }

    public function registerConnection(ResponseEvent $responseEvent): void
    {
        $request = $responseEvent->getRequest();

        $rawApplicationId = $request->query->get('applicationId') ?? $request->getPayload()->get('applicationId');

        if (null === $rawApplicationId) {
            return;
        }

        $applicationId = ApplicationId::fromString($rawApplicationId);

        $this->recordConnection->for(
            $applicationId,
            ApplicationType::Unknown,
            ConnectionType::API,
            IpAddress::fromString($request->server->get('REMOTE_ADDR'))
        );
    }
}
