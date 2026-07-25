<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Infrastructure\Factory\MessageDecoder;
use App\Infrastructure\Message\NewEvent as NewEventMessage;
use Evenement\EventEmitter;
use PearTreeWeb\EventSourcerer\Common\Model\MessagePattern;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use React\Socket\ConnectionInterface;
use React\Stream\ThroughStream;
use React\Stream\WritableStreamInterface;

final class SocketConnection extends EventEmitter implements ConnectionInterface
{
    private function __construct(private readonly string $address) {}

    public static function create(string $address): self
    {
        return new self($address);
    }

    public function isReadable(): bool
    {
        return false;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function pause(): void
    {
    }

    public function resume(): void
    {
    }

    public function pipe(WritableStreamInterface $dest, array $options = array()): WritableStreamInterface
    {
        return new ThroughStream();
    }

    public function write($data): bool
    {
        return true;
    }

    public function end($data = null): void
    {
    }

    public function close(): void
    {
    }

    public function getRemoteAddress(): string
    {
        return $this->address . '-remote';
    }

    public function getLocalAddress(): string
    {
        return $this->address . '-local-address';
    }

    public function sendMockAcknowledgementOfNewEvent($event): void
    {
        /** @var NewEventMessage[] $decoded */
        $decoded = MessageDecoder::decode(MessageType::NewEvent, $event);
        foreach ($decoded as $decodedMessage) {
            $this->emit('data', [self::acknowledgementPayload($decodedMessage)]);
        }
    }

    /**
     * This only happens in the client code, so we have to manually emit
     */
    private static function acknowledgementPayload(NewEventMessage $message): string
    {
        return CreateMessage::forAcknowledgement(
            $message->streamId,
            $message->streamId,
            Id::applicationId(),
            Id::workerId(),
            $message->checkpoint,
            $message->checkpoint,
        )->toString();
    }
}
