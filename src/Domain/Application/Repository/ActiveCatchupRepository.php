<?php

declare(strict_types=1);

namespace App\Domain\Application\Repository;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

interface ActiveCatchupRepository
{
    public function addFor(ApplicationId $applicationId): void;

    public function hasFor(ApplicationId $applicationId): bool;

    public function removeFor(ApplicationId $applicationId): void;

    public function isStaleFor(ApplicationId $applicationId): bool;
}
