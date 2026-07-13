<?php

declare(strict_types=1);

namespace App\Extension\Packages\Connections\Widget;

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
final readonly class Connections extends AbstractWidget implements Widget
{
    private const string STREAM_ID = 'connections';
    private const string WIDGET_NAME = 'Connections';

    public static function name(): WidgetName
    {
        return WidgetName::fromString(self::WIDGET_NAME);
    }

    protected function projectionName(): ?ProjectionName
    {
        return null;
    }

    protected function streamId(): StreamId
    {
        return StreamId::fromString(self::STREAM_ID);
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
