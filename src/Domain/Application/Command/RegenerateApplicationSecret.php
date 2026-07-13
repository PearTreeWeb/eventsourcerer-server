<?php

declare(strict_types=1);

namespace App\Domain\Application\Command;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

final readonly class RegenerateApplicationSecret
{
    public function __construct(public ApplicationId $id) {}
}
