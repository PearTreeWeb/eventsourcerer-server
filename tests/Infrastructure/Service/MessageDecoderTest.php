<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Factory\MessageDecoder;
use App\Infrastructure\Message\Acknowledgement;
use App\Infrastructure\Message\CatchupRequest;
use App\Infrastructure\Message\NewEvent;
use App\Infrastructure\Message\ProvideIdentity;
use App\Infrastructure\Message\ReadStream;
use App\Infrastructure\Message\Rejection;
use App\Infrastructure\Message\WriteNewEvent;
use App\Tests\Double\Id;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Model\EventVersion;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageDecoderTest extends TestCase
{
    #[Test]
    public function itCanDecodeCatchupRequestMessage(): void
    {
        $streamId = StreamId::allStream();
        $applicationId = Id::applicationId();
        $workerId = Id::workerId();

        $message = CreateMessage::forCatchupRequest($streamId, $applicationId, $workerId)->toString();

        $this->assertEquals(
            new CatchupRequest($streamId, $applicationId, $workerId),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::CatchupRequest, $message)
            )[0]
        );
    }

    #[Test]
    public function itCanDecodeAcknowledgementMessage(): void
    {
        $streamId = StreamId::allStream();
        $applicationId = Id::applicationId();
        $workerId = Id::workerId();
        $checkpoint = Checkpoint::fromInt(3);
        $allStreamCheckpoint = Checkpoint::fromInt(5);

        $message = CreateMessage::forAcknowledgement(
            $streamId,
            $streamId,
            $applicationId,
            $workerId,
            $checkpoint,
            $allStreamCheckpoint,
        );

        $this->assertEquals(
            new Acknowledgement(
                $streamId,
                $streamId,
                $applicationId,
                $workerId,
                $checkpoint,
                $allStreamCheckpoint,
            ),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::Acknowledgement, $message->toString())
            )[0]
        );
    }

    #[Test]
    public function itCanDecodeProvideIdentityMessage(): void
    {
        $applicationId = Id::applicationId();
        $applicationType = ApplicationType::Symfony;
        $workerId = Id::workerId();

        $message = CreateMessage::forProvidingIdentity(
            $applicationId,
            $applicationType,
            $workerId,
        );

        $this->assertEquals(
            new ProvideIdentity(
                $applicationId,
                $applicationType,
                $workerId,
            ),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::ProvideIdentity, $message->toString())
            )[0]
        );
    }

    #[Test]
    public function testItCanDecodeWriteNewEventMessage(): void
    {
        $this->assertEquals(
            new WriteNewEvent(
                StreamId::fromString('basket-cedfffdf-6ac2-450a-84c2-3858d03b97f4'),
                EventName::fromString('item-added-to-basket'),
                EventVersion::fromInt(1),
                [
                    'price' => '3000',
                    'name' => 'new hat 4',
                ],
                [],
                9,
            ),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::WriteNewEvent, 'WRITE_NEW_EVENT basket-cedfffdf-6ac2-450a-84c2-3858d03b97f4 item-added-to-basket 1 {"price":"3000","name":"new hat 4"}, {"expectedVersion":"9"} 9')
            )[0]
        );
    }

    #[Test]
    public function itCanDecodeRejectionMessage(): void
    {
        $streamId = StreamId::fromString('basket-cedfffdf-6ac2-450a-84c2-3858d03b97f4');
        $checkpoint = Checkpoint::fromInt(21);
        $applicationId = Id::applicationId();

        $message = CreateMessage::forRejection(
            $streamId,
            $applicationId,
            $checkpoint,
            self::sampleEventJson(),
        );

        $this->assertEquals(
            new Rejection(
                new NewEvent(self::sampleEventJson(), $streamId, $checkpoint),
            ),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::RejectEvent, $message->toString())
            )[0]
        );
    }

    #[Test]
    public function itCanDecodeReadStreamMessage(): void
    {
        $streamId = StreamId::allStream();
        $applicationId = Id::applicationId();
        $checkpoint = Checkpoint::zero();
        $message = CreateMessage::forReadingStream($streamId, $applicationId, $checkpoint);

        $this->assertEquals(
            new ReadStream($applicationId, $streamId, $checkpoint, $checkpoint),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::ReadStream, $message->toString())
            )[0]
        );
    }

    #[Test]
    public function itCanDecodeNewEventMessage(): void
    {
        $message = CreateMessage::forNewEvent(self::sampleEventJson());

        $this->assertEquals(
            new NewEvent(
                self::sampleEventJson(),
                StreamId::fromString('basket-cedfffdf-6ac2-450a-84c2-3858d03b97f4'),
                Checkpoint::fromInt(21),
            ),
            \iterator_to_array(
                MessageDecoder::decode(MessageType::NewEvent, $message->toString())
            )[0]
        );
    }

    private static function sampleEventJson(): string
    {
        return '{"eventVersion":1,"name":"item-added-to-basket","number":21,"payload":{"price":"3000","name":"new hat 4"},"stream":"basket-cedfffdf-6ac2-450a-84c2-3858d03b97f4","occurred":"2025-02-13T21:24:41+00:00"}';
    }
}
