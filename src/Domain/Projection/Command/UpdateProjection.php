<?php

declare(strict_types=1);

namespace App\Domain\Projection\Command;

use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(autowire: false)]
final readonly class UpdateProjection
{
    public function __construct(
        public ProjectionId $id,
        public ProjectionName $name,
        public ProjectionEventProperties $properties,
        public bool $partition,
        public bool $exposeStateViaApi,
        public ?\DateTimeImmutable $startDate = null,
        public ?\DateTimeImmutable $endDate = null,
    ) {}
}
