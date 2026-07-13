<?php

declare(strict_types=1);

namespace App\Domain\Client\Model;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class ApplicationWorker
{
    public function __construct(public ApplicationId $applicationId, public WorkerId $workerId) {}
}
