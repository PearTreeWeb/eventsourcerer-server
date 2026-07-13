<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use App\Infrastructure\Message\Acknowledgement;
use App\Infrastructure\Message\CatchupRequest;
use App\Infrastructure\Message\Message;
use App\Infrastructure\Message\NewEvent;
use App\Infrastructure\Message\ProvideIdentity;
use App\Infrastructure\Message\ReadStream;
use App\Infrastructure\Message\Rejection;
use App\Infrastructure\Message\WriteNewEvent;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Model\EventVersion;
use PearTreeWeb\EventSourcerer\Common\Model\MessageMarkup;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class MessageDecoder
{
    /**
     * @return iterable<Message>
     */
    public static function decode(MessageType $messageType, string $rawMessage): iterable
    {
        $messages = explode(MessageMarkup::NewEventParser->value, $rawMessage);

        $formattedMessages = [];

        foreach ($messages as $message) {
            if (empty($message)) {
                continue;
            }

            $matched = preg_match_all('/{(?:[^{}]*|(?R))*}/', $message, $jsonParts);

            $messageParts = '';

            if (false !== $matched) {
                foreach ($jsonParts as $jsonPart) {
                    $messageParts = str_replace($jsonPart, '', $message);
                }
            }

            $messageParts = str_replace(',', '', $messageParts);
            $messageParts = explode(' ', $messageParts);
            $messageParts = array_values(array_filter($messageParts, static fn ($value) => $value !== ''));

            if (!isset($messageParts[1]) && MessageType::NewEvent !== $messageType) {
                continue;
            }

            $message = match ($messageType) {
                MessageType::CatchupRequest   => self::catchupRequest($messageParts),
                MessageType::Acknowledgement  => self::acknowledgement($messageParts),
                MessageType::ProvideIdentity  => self::provideIdentity($messageParts),
                MessageType::ReadStream       => self::readStream($messageParts),
                MessageType::RejectEvent      => self::reject($message),
                MessageType::NewEvent         => self::newEvent($jsonParts[0]),
                MessageType::WriteNewEvent    => self::writeNewEvent($messageParts, $jsonParts[0]),
                MessageType::NewEventAccepted,
                MessageType::NewEventRejected => null,
            };

            if (null !== $message) {
                $formattedMessages[] = $message;
            }
        }

        return $formattedMessages;
    }

    /**
     * @param array{
     *     0: mixed,
     *     1: mixed,
     *     2: mixed,
     *     3: mixed,
     *     4: mixed,
     *     5: mixed,
     *     6: mixed,
     * } $messageParts
     */
    private static function acknowledgement(array $messageParts): ?Message
    {
        if (
            !isset(
                $messageParts[1],
                $messageParts[2],
                $messageParts[3],
                $messageParts[4],
                $messageParts[5],
                $messageParts[6],
            )
        ) {
            return null;
        }

        return new Acknowledgement(
            StreamId::fromString($messageParts[1]),
            StreamId::fromString($messageParts[2]),
            ApplicationId::fromString($messageParts[3]),
            WorkerId::fromString($messageParts[4]),
            Checkpoint::fromString($messageParts[5]),
            Checkpoint::fromString($messageParts[6])
        );
    }

    /**
     * @param array{1: string, 2: string, 3: string, 4?: string} $messageParts
     */
    private static function catchupRequest(array $messageParts): Message
    {
        $checkpoint = !empty($messageParts[4])
            ? Checkpoint::fromString($messageParts[4])
            : null;

        return new CatchupRequest(
            StreamId::fromString($messageParts[1]),
            ApplicationId::fromString($messageParts[2]),
            WorkerId::fromString($messageParts[3]),
            $checkpoint
        );
    }

    /**
     * @param array{1: string, 2: string, 3: string} $messageParts
     */
    private static function provideIdentity(array $messageParts): Message
    {
        return new ProvideIdentity(
            ApplicationId::fromString($messageParts[1]),
            ApplicationType::from($messageParts[2]),
            WorkerId::fromString($messageParts[3])
        );
    }

    private static function reject(string $message): Message
    {
        return new Rejection(self::originalMessage($message));
    }

    private static function originalMessage(string $message): NewEvent
    {
        preg_match(
            sprintf(
                '/%s (.*) %s/',
                MessageMarkup::RejectedEventStart->value,
                MessageMarkup::RejectedEventEnd->value
            ),
            $message,
            $matches
        );

        $payload = trim(
           str_replace(
               [
                   MessageMarkup::RejectedEventStart->value,
                   MessageMarkup::RejectedEventEnd->value,
               ],
               '',
               $matches[0]
           )
        );

        $decodedPayload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return new NewEvent(
            $payload,
            StreamId::fromString($decodedPayload['stream']),
            Checkpoint::fromInt($decodedPayload['number'])
        );
    }

    /**
     * @param array<int, string> $messageParts
     * @param array{0: string} $jsonParts
     */
    private static function writeNewEvent(array $messageParts, array $jsonParts): Message
    {
        $expectedVersion = isset($messageParts[6])
            ? (int) $messageParts[6]
            : null;

        return new WriteNewEvent(
            StreamId::fromString($messageParts[1]),
            EventName::fromString($messageParts[2]),
            EventVersion::fromString($messageParts[3]),
            json_decode($jsonParts[0], true, 512, JSON_THROW_ON_ERROR),
            [],
            $expectedVersion,
        );
    }

    /**
     * @param array<int, string> $messageParts
     */
    private static function readStream(array $messageParts): Message
    {
        $start = isset($messageParts[3])
            ? Checkpoint::fromString((string) $messageParts[3])
            : null;

        $end = isset($messageParts[4])
            ? Checkpoint::fromString((string) $messageParts[4])
            : null;

        return new ReadStream(
            ApplicationId::fromString($messageParts[2]),
            StreamId::fromString($messageParts[1]),
            $start,
            $end
        );
    }

    /**
     * @param array<string> $json
     */
    private static function newEvent(array $json): Message
    {
        $decoded = json_decode($json[0], true, 512, JSON_THROW_ON_ERROR);

        return new NewEvent(
            $json[0],
            StreamId::fromString($decoded['stream']),
            Checkpoint::fromInt($decoded['number'])
        );
    }
}
