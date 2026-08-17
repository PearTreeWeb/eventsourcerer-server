<?php

declare(strict_types=1);

namespace App\Extension\Packages\MicroManager\PropertyType;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use App\Extension\Packages\MicroManager\Author\MicroManager;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class OrchestrationId implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'Orchestration ID';
    private const string PACKAGE = 'MicroServices';

    public static function author(): Author
    {
        return MicroManager::author();
    }

    public static function create(): PropertyType
    {
        return new self();
    }

    public static function name(): PropertyTypeDescription
    {
        return PropertyTypeDescription::fromString(self::DESCRIPTION);
    }

    public static function serialize(mixed $value): string
    {
        return $value;
    }

    public static function deserialize(string $value): mixed
    {
        return $value;
    }

    public static function validate(mixed $value): void
    {
    }

    public static function conditionOperators(): array
    {
        return [];
    }

    public static function package(): Package
    {
        return Package::fromString(self::PACKAGE);
    }

    public static function toString(string $value): string
    {
        return $value;
    }

    public static function exampleInput(): string
    {
        return 'orchestration-123';
    }
}
