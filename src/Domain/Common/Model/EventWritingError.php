<?php

namespace App\Domain\Common\Model;

final readonly class EventWritingError implements SystemError
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private array $payload,
        private \Throwable $thrown
    ) {}

    public function message(): ErrorMessage
    {
        return ErrorMessage::fromString(
            sprintf(
                'An exception was thrown whilst attempting to write a stream event: %s. Payload provided was: %s',
                $this->thrown->getMessage(),
                json_encode($this->payload)
            )
        );
    }

    public function __toString(): string
    {
        return $this->message()->toString();
    }
}
