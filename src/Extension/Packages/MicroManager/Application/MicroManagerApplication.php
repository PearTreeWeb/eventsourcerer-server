<?php

declare(strict_types=1);

namespace App\Extension\Packages\MicroManager\Application;

use App\Domain\Application\Model\Application;
use App\Domain\Application\Model\ApplicationName;
use App\Extension\Packages\MicroManager\Author\MicroManager;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.application')]
final readonly class MicroManagerApplication implements Application
{
    private const string APPLICATION_ID = 'a511cf2b-abaa-4274-b3ee-b2dd086dc50f';

    public static function name(): ApplicationName
    {
        return ApplicationName::fromString(MicroManager::author()->toString());
    }

    public static function id(): ApplicationId
    {
        return ApplicationId::fromString(self::APPLICATION_ID);
    }
}
