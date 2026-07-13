<?php

declare(strict_types=1);

namespace App\Domain\Connection\Model;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class Connection
{
    public function __construct(
        public ApplicationId $applicationId,
        public WorkerId $workerId,
    ) {}
}
