<?php

declare(strict_types=1);

namespace App\Extension\Default\Event;

use App\Domain\Common\Model\Author;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventTemplate;
use App\Extension\Default\PropertyType\DateTimeType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.event_template')]
final class DayPassed implements EventTemplate
{
    public const string DATE_EVENT_PROPERTY_ID = '84fb5b0f-10be-429c-add2-584520286fba';
    public const string DATE_EVENT_PROPERTY_NAME = 'date';
    public const string EVENT_ID = '30e48a18-af46-4c03-97d6-eb3a92447c3e';
    public const string EVENT_NAME = 'day-passed';
    private const int TOMBSTONE_AFTER_N_SECONDS = 0; // never

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
                EventPropertyId::fromString(self::DATE_EVENT_PROPERTY_ID),
                EventPropertyName::fromString(self::DATE_EVENT_PROPERTY_NAME),
                DateTimeType::create(),
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
        return self::TOMBSTONE_AFTER_N_SECONDS;
    }

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }
}
