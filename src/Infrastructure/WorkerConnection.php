<?php

declare(strict_types=1);

namespace App\Infrastructure;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use React\Socket\ConnectionInterface;

final readonly class WorkerConnection
{
    public function __construct(
        public ApplicationId $applicationId,
        public WorkerId $workerId,
        public ConnectionInterface $connection,
    ) {}
}
