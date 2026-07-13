<?php

declare(strict_types=1);

namespace App\Extension\Packages\EventStats\Widget;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Widget\Model\WidgetName;
use App\Extension\Default\Widget\AbstractWidget;
use App\Extension\Default\Widget\Widget;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.widget')]
final readonly class EventStats extends AbstractWidget implements Widget
{
    private const string NAME = 'Event Stats';
    private const string PROJECTION_NAME = 'Event Stats';

    protected function projectionName(): ProjectionName
    {
        return ProjectionName::fromString(self::PROJECTION_NAME);
    }

    public static function name(): WidgetName
    {
        return WidgetName::fromString(self::NAME);
    }

    protected function streamId(): ?StreamId
    {
        return null;
    }

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Base->value);
    }
}
