<?php

declare(strict_types=1);

namespace App\Domain\Application\Query;

use App\Domain\Common\Model\Query;
use App\Entity\Application;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

/**
 * @implements Query<Application>
 */
final readonly class GetApplication implements Query
{
    public function __construct(public ApplicationId $id) {}
}
