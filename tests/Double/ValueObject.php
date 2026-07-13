<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\User;
use App\Extension\Default\PropertyType\Boolean;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final class ValueObject
{
    public static function emailAddress(): EmailAddress
    {
        return EmailAddress::fromString('montyburns@theburnsempire.com');
    }

    public static function eventName(): EventName
    {
        return EventName::fromString('test event name');
    }

    public static function eventVersion(): EventVersion
    {
        return EventVersion::fromInt(1);
    }

    public static function createdAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public static function eventProperties(): EventProperties
    {
        return EventProperties::fromArray([self::eventProperty()]);
    }

    public static function eventProperty(): EventProperty
    {
        return new EventProperty(
            Id::eventPropertyId(),
            self::eventPropertyName(),
            Boolean::create(),
            false
        );
    }

    public static function eventPropertyName(): EventPropertyName
    {
        return EventPropertyName::fromString('test event property name');
    }

    public static function projectionName(): ProjectionName
    {
        return ProjectionName::fromString('test');
    }

    public static function projectionEventProperties(): ProjectionEventProperties
    {
        return ProjectionEventProperties::fromArray([
            new ProjectionEventProperty(
                Id::projectionEventPropertyId(),
                self::eventPropertyName(),
                self::projectionPropertyType()
            )
        ]);
    }

    public static function projectionPropertyType(): PropertyType
    {
        return Boolean::create();
    }

    public static function user(): User
    {
        return new User(
            Id::userId(),
            self::emailAddress(),
            Role::USER
        );
    }
}
