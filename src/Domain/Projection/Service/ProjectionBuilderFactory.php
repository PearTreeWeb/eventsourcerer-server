<?php

declare(strict_types=1);

namespace App\Domain\Projection\Service;

use App\Domain\Projection\Model\ProjectionName;

interface ProjectionBuilderFactory
{
    public function create(ProjectionName $name): ProjectionBuilder;

}
