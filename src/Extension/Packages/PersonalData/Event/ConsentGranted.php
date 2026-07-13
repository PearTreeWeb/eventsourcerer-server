<?php

declare(strict_types=1);

namespace App\Extension\Packages\PersonalData\Event;

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
final readonly class ConsentGranted implements EventTemplate
{
    private const string DATE_CONSENT_GIVEN_PROPERTY_ID = 'ecb70a93-81f7-4a0a-b654-9528172e23ae';
    private const string DATE_CONSENT_GIVEN_PROPERTY_NAME = 'date-consent-given';
    private const string EVENT_ID = 'fe4fe11b-a07a-4c1f-9530-08d497d6f9d3';
    private const string EVENT_NAME = 'consent-granted';

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
                EventPropertyId::fromString(self::DATE_CONSENT_GIVEN_PROPERTY_ID),
                EventPropertyName::fromString(self::DATE_CONSENT_GIVEN_PROPERTY_NAME),
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
}
