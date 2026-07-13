<?php

declare(strict_types=1);

namespace App\Domain\Widget\Query;

use App\Domain\Common\Model\Query;

/**
 * @implements Query<string[]>
 */
final readonly class GetAllRegisteredProjections implements Query
{
}
