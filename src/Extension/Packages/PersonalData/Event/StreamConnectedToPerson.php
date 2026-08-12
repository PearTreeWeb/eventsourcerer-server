<?php

declare(strict_types=1);

namespace App\Extension\Packages\PersonalData\Event;

use App\Domain\Common\Model\Author;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventTemplate;
use App\Extension\Packages\PersonalData\PropertyType\PersonUniqueIdentifier;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.event_template')]
final class StreamConnectedToPerson implements EventTemplate
{
    private const string EVENT_ID = '1cfda851-373a-4e89-81dd-4587e434d214';
    private const string EVENT_NAME = 'stream-connected-to-person';
    private const string PERSON_IDENTIFIER_EVENT_PROPERTY_ID = '14002c21-74a6-41d9-bb9f-b7df5bb6de0a';
    private const string PERSON_IDENTIFIER_EVENT_PROPERTY_NAME = 'person-unique-identifier';

    public static function id(): EventId
    {
        return EventId::fromString(self::EVENT_ID);
    }

    public static function name(): EventName
    {
        return EventName::fromString(self::EVENT_NAME);
    }

    public static function eventProperties(): EventProperties
    {
        return EventProperties::fromArray([
            new EventProperty(
                EventPropertyId::fromString(self::PERSON_IDENTIFIER_EVENT_PROPERTY_ID),
                EventPropertyName::fromString(self::PERSON_IDENTIFIER_EVENT_PROPERTY_NAME),
                PersonUniqueIdentifier::create(),
                false,
            ),
        ]);
    }

    public static function isSystemEvent(): bool
    {
        return true;
    }

    public static function tombstoneAfterNSeconds(): int
    {
        return 0;
    }

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }
}
