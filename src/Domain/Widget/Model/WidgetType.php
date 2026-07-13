<?php

declare(strict_types=1);

namespace App\Domain\Widget\Model;

enum WidgetType: string
{
    case Chart = 'Chart';
    case Map   = 'Map';
    case Table = 'Table';
}
