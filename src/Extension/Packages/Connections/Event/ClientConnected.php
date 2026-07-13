<?php

declare(strict_types=1);

namespace App\Extension\Packages\Connections\Event;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventTemplate;
use App\Extension\Packages\Connections\PropertyType\ApplicationType;
use App\Extension\Packages\Connections\PropertyType\IPAddress;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.event_template')]
final readonly class ClientConnected implements EventTemplate
{
    public const string APPLICATION_TYPE_EVENT_PROPERTY_ID = '02558791-d24b-4e59-946c-e956033a4ed7';
    public const string APPLICATION_TYPE_EVENT_PROPERTY_NAME = 'application-type';
    public const string EVENT_ID = '1cfda851-373a-4e89-81dd-4587e434d214';
    public const string EVENT_NAME = 'client-connected';
    public const string IP_ADDRESS_EVENT_PROPERTY_ID = 'e390ebea-a557-4889-af60-9c814b162901';
    public const string IP_ADDRESS_EVENT_PROPERTY_NAME = 'ip-address';
    private const int TOMBSTONE_AFTER_N_SECONDS = 86_400; // 1 day

    public static function name(): EventName
    {
        return EventName::fromString(self::EVENT_NAME);
    }

    public static function eventProperties(): EventProperties
    {
        return EventProperties::fromArray([
            new EventProperty(
                EventPropertyId::fromString(self::IP_ADDRESS_EVENT_PROPERTY_ID),
                EventPropertyName::fromString(self::IP_ADDRESS_EVENT_PROPERTY_NAME),
                IPAddress::create(),
                false,
            ),
            new EventProperty(
                EventPropertyId::fromString(self::APPLICATION_TYPE_EVENT_PROPERTY_ID),
                EventPropertyName::fromString(self::APPLICATION_TYPE_EVENT_PROPERTY_NAME),
                ApplicationType::create(),
                false,
            )
        ]);
    }

    public static function id(): EventId
    {
        return EventId::fromString(self::EVENT_ID);
    }

    public static function isSystemEvent(): bool
    {
        return true;
    }

    public static function tombstoneAfterNSeconds(): int
    {
        return self::TOMBSTONE_AFTER_N_SECONDS;
    }
}
