<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, string>
 */
final class ConditionParameterValues implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, string>
     */
    use FulfilIsCollection;

    public function add(mixed $item): void
    {
        $this->items->add($item);
    }
}
