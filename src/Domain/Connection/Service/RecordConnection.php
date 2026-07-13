<?php

declare(strict_types=1);

namespace App\Domain\Connection\Service;

use App\Domain\Common\Model\IpAddress;
use App\Domain\Connection\Model\ConnectionType;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;

interface RecordConnection
{
    public function for(
        ApplicationId $applicationId,
        ApplicationType $applicationType,
        ConnectionType $connectionType,
        IpAddress $ipAddress
    ): void;
}
