<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, Condition>
 */
final class Conditions implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, Condition>
     */
    use FulfilIsCollection;
}
