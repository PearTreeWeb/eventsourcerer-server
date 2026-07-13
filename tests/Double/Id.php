<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\Application\Model\ApplicationName;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Stream\Model\StreamEventId;
use App\Domain\User\Model\UserId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class Id
{
    public static function eventId(): EventId
    {
        return EventId::null();
    }

    public static function eventPropertyId(): EventPropertyId
    {
        return EventPropertyId::null();
    }

    public static function streamId(): StreamId
    {
        return StreamId::fromString('a3d60a59-0d17-4a33-a147-15c9965384ee');
    }

    public static function streamId2(): StreamId
    {
        return StreamId::fromString('49e7aadc-a896-444a-b662-6dd23717d845');
    }

    public static function streamEventId(): StreamEventId
    {
        return StreamEventId::null();
    }

    public static function userId(): UserId
    {
        return UserId::null();
    }

    public static function projectionId(): ProjectionId
    {
        return ProjectionId::null();
    }

    public static function projectionEventPropertyId(): ProjectionEventPropertyId
    {
        return ProjectionEventPropertyId::null();
    }

    public static function applicationId(): ApplicationId
    {
        return ApplicationId::fromString('b1359671-c956-42b5-8044-644e72171e85');
    }

    public static function streamIdAll(): StreamId
    {
        return StreamId::fromString('*');
    }

    public static function workerId(): WorkerId
    {
        return WorkerId::fromString('worker1');
    }

    public static function eventName(): EventName
    {
        return EventName::fromString('Test Event');
    }

    public static function eventVersion(): EventVersion
    {
        return EventVersion::fromInt(1);
    }

    public static function applicationName(): ApplicationName
    {
        return ApplicationName::fromString('Test Application');
    }
}
