<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Command;

use App\Tests\Double\Id;
use App\Tests\Infrastructure\SocketServer\SocketServerTestCase;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use PHPUnit\Framework\Attributes\Test;

final class SocketServerTest extends SocketServerTestCase
{
    #[Test]
    public function itSendsEventAgainAfterRejection(): void
    {
        $payload1 = self::newEventPayload(1);
        $payload2 = self::newEventPayload(2);
        $payload3 = self::newEventPayload(3);
        $payload4 = self::newEventPayload(4);
        $payload5 = self::newEventPayload(5);
        $payload6 = self::newEventPayload(6);

        $this->eventEmitter->emit('connection', [$this->mockConnection]);
        $this->eventEmitter->emit('connection', [$this->mockConnectionConsumer]);
        
        $producerWorkerId = \PearTreeWeb\EventSourcerer\Common\Model\WorkerId::fromString('worker-producer');
        $consumerWorkerId = \PearTreeWeb\EventSourcerer\Common\Model\WorkerId::fromString('worker-consumer');
        $applicationId = Id::applicationId();

        $this->mockConnection->emit('data', [CreateMessage::forProvidingIdentity($applicationId, ApplicationType::Bespoke, $producerWorkerId)->toString()]);
        $this->mockConnectionConsumer->emit('data', [CreateMessage::forProvidingIdentity($applicationId, ApplicationType::Bespoke, $consumerWorkerId)->toString()]);
        
        $this->mockConnection->emit('data', [$payload5]);
        $this->mockConnection->emit('data', [$payload6]);
        $this->mockConnection->emit('data', [$payload1]);
        $this->mockConnection->emit('data', [$payload2]);
        $this->mockConnection->emit('data', [$payload3]);
        $this->mockConnection->emit('data', [$payload4]);

        $this->assertReceivedEventsInOrder([
            $payload1,
            $payload2,
            $payload3,
            $payload4,
            $payload5,
            $payload6,
        ]);
    }

    private static function newEventPayload(int $checkpoint): string
    {
        return CreateMessage::forNewEvent(
            sprintf(
                '{"eventVersion":1,"name":"item-added-to-basket","allSequence":1,"number":%d,"payload":{"price":"3000","name":"new hat 3"},"stream":"basket-acb99083-add1-4f1b-acd4-c85ecc3deb3a","occurred":"2025-02-11T06:44:28+00:00","workerId":"worker-consumer"}',
                $checkpoint,
            )
        )->toString();
    }

    private static function identityPayload(string $workerId): string
    {
        return CreateMessage::forProvidingIdentity(
            Id::applicationId(),
            ApplicationType::Bespoke,
            \PearTreeWeb\EventSourcerer\Common\Model\WorkerId::fromString($workerId),
        )->toString();
    }
}
