<?php

declare(strict_types=1);

namespace App\Infrastructure;

use ApiPlatform\Metadata\Post;
use App\ApiDto\StreamEvent;
use App\Domain\Common\Model\IpAddress;
use App\Domain\Connection\Model\ConnectionType;
use App\Domain\Connection\Service\RecordConnection as RecordConnectionInterface;
use App\Extension\Packages\Connections\Event\ClientConnected;
use App\Processor\StreamEventProcessor;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;

final readonly class RecordConnection implements RecordConnectionInterface
{
    private const string STREAM_ID = 'connections';

    public function __construct(private StreamEventProcessor $streamEventProcessor) {}

    public function for(
        ApplicationId $applicationId,
        ApplicationType $applicationType,
        ConnectionType $connectionType,
        IpAddress $ipAddress
    ): void {
        $properties = [
            ClientConnected::IP_ADDRESS_EVENT_PROPERTY_NAME => $ipAddress->toString(),
            ClientConnected::APPLICATION_TYPE_EVENT_PROPERTY_NAME => $applicationType->value
        ];

        $dto = new StreamEvent();
        $dto->stream = self::STREAM_ID;
        $dto->properties = $properties;
        $dto->event = ClientConnected::EVENT_NAME;
        $dto->version = 1;

        $this->streamEventProcessor->process($dto, new Post());
    }
}
