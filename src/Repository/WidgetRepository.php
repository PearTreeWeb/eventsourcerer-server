<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Widget\Exception\WidgetDoesNotExist;
use App\Domain\Widget\Model\WidgetName;
use App\Extension\Default\Widget\Widget;

final readonly class WidgetRepository
{
    /**
     * @param iterable<Widget> $widgets
     */
    public function __construct(private iterable $widgets) {}

    /**
     * @return iterable<string>
     */
    public function allRegisteredProjections(): iterable
    {
        foreach ($this->widgets as $widget) {
            yield $widget::name()->toString();
        }
    }

    public function findStrict(WidgetName $name): Widget
    {
        $widget = collect($this->widgets)
            ->first(static fn (Widget $widget) => $widget::name()->sameAs($name));

        return $widget ?? throw WidgetDoesNotExist::withName($name->toString());
    }
}
