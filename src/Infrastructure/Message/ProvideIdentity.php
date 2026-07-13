<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final readonly class ProvideIdentity implements Message
{
    public function __construct(
        public ApplicationId $applicationId,
        public ApplicationType $applicationType,
        public WorkerId $workerId,
    ) {}
}
