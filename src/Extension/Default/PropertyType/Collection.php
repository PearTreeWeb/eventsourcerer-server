<?php

namespace App\Extension\Default\PropertyType;

use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final class Collection extends SimpleArray
{
    protected const string DESCRIPTION = 'Collection';

    public static function exampleInput(): string
    {
        return '[
            \'candidate-17921e77-8a0e-4857-8366-6d53e79a31b9\',
            \'candidate-8ee263c0-59f1-4d5e-90c5-e6bdaf111078\',
        ]';
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Network->value);
    }
}