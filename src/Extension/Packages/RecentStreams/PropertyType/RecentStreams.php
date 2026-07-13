<?php

declare(strict_types=1);

namespace App\Extension\Packages\RecentStreams\PropertyType;

use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Extension\Default\PropertyType\SimpleArray;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final class RecentStreams extends SimpleArray
{
    protected const string DESCRIPTION = 'Recent Streams';

    public static function exampleInput(): string
    {
        return '[
            \'basket-17921e77-8a0e-4857-8366-6d53e79a31b9\',
            \'basket-8ee263c0-59f1-4d5e-90c5-e6bdaf111078\',
        ]';
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Network->value);
    }
}
