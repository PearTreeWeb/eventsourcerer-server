<?php

declare(strict_types=1);

namespace App\Domain\Widget\Command;

use App\Domain\Widget\Model\WidgetName;
use App\Domain\Widget\Model\WidgetType;
use App\Entity\Projection;

final readonly class RegisterWidget
{
    public function __construct(
        public WidgetName $name,
        public WidgetType $type,
        public Projection $projection
    ) {}
}
