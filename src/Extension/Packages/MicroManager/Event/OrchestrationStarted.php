<?php

declare(strict_types=1);

namespace App\Extension\Packages\MicroManager\Event;

use App\Domain\Common\Model\Author;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventTemplate;
use App\Extension\Default\PropertyType\Text;
use App\Extension\Packages\MicroManager\Author\MicroManager;
use App\Extension\Packages\MicroManager\PropertyType\OrchestrationId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.event_template')]
final class OrchestrationStarted implements EventTemplate
{
    public const string EVENT_ID = '2c79cafa-4900-4013-aaa2-4912c0372a6b';
    public const string EVENT_NAME = 'orchestration-started';
    private const int TOMBSTONE_AFTER_N_SECONDS = 0; // never
    public const string ORCHESTRATION_ID_PROPERTY_ID = '6d418ae3-757a-47e5-9576-4613774fab69';
    private const string ORCHESTRATION_ID_PROPERTY_NAME = 'orchestration-id';
    private const string PROJECT_ID_PROPERTY_ID = '18b80b8b-3806-4aba-bd94-46a24478c783';
    private const string PROJECT_ID_PROPERTY_NAME = 'project-id';

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
                EventPropertyId::fromString(self::ORCHESTRATION_ID_PROPERTY_ID),
                EventPropertyName::fromString(self::ORCHESTRATION_ID_PROPERTY_NAME),
                OrchestrationId::create(),
                false,
            ),
            new EventProperty(
                EventPropertyId::fromString(self::PROJECT_ID_PROPERTY_ID),
                EventPropertyName::fromString(self::PROJECT_ID_PROPERTY_NAME),
                Text::create(),
                false,
            ),
        ]);
    }

    public static function isSystemEvent(): bool
    {
        return false;
    }

    public static function tombstoneAfterNSeconds(): int
    {
        return self::TOMBSTONE_AFTER_N_SECONDS;
    }

    public static function author(): Author
    {
        return MicroManager::author();
    }
}
