<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Domain\User\Model\Role;
use App\Domain\User\Model\UserId;
use App\Entity\Event;
use App\Entity\EventProperty;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

final readonly class Entity
{
    public static function event(): Event
    {
        $event = Event::create(
            Id::eventId(),
            ValueObject::eventName()->toString(),
            0,
            ValueObject::createdAt()
        );

        $event->setProperties(new ArrayCollection([self::eventProperty()]));

        return $event;
    }

    public static function user(?UserId $id = null): User
    {
        return User::create(
            $id ?? UserId::null(),
            ValueObject::emailAddress(),
            Role::SUPER_USER
        );
    }

    public static function eventProperty(): EventProperty
    {
        return EventProperty::create(ValueObject::eventProperty(), ValueObject::createdAt());
    }

    public static function streamEvent(): StreamEvent
    {
        return StreamEvent::create(
            Id::streamEventId(),
            Id::eventId(),
            Id::streamId(),
            ValueObject::eventName(),
            ValueObject::eventVersion(),
            new ArrayCollection(),
            self::stream(),
            ValueObject::createdAt(),
        );
    }

    public static function stream(): Stream
    {
        return Stream::create(
            Id::streamId(),
            ValueObject::createdAt()
        );
    }

    public static function userSuper(): User
    {
        return User::create(Id::userId(), ValueObject::emailAddress(), Role::SUPER_USER);
    }
}
