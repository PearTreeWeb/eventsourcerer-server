<?php

declare(strict_types=1);

namespace App\Domain\Settings\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Settings;

/**
 * @implements Query<?Settings>
 */
final readonly class GetSettings implements Query
{
}
