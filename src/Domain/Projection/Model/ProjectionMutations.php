<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, ProjectionMutation>
 */
final class ProjectionMutations implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, ProjectionMutation> $items
     */
    use FulfilIsCollection;
}
