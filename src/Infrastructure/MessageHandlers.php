<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Illuminate\Support\Collection;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;

final readonly class MessageHandlers
{
    /**
     * @param Collection<string, MessageHandler> $handlers
     */
    private function __construct(
        private Collection $handlers
    ) {}

    /**
     * @param iterable<MessageHandler> $handlers
     */
    public static function create(iterable $handlers): self
    {
        return new self(new Collection($handlers));
    }

    public function add(MessageHandler $handler): void
    {
        $this->handlers->put($handler::handles()->value, $handler);
    }

    public function findFor(MessageType $messageType): ?MessageHandler
    {
        return $this->handlers->first(
            static fn (MessageHandler $handler): bool => $handler->canHandle($messageType)
        );
    }
}
