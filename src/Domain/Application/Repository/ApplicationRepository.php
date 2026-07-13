<?php

namespace App\Domain\Application\Repository;

use App\Entity\Application;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

interface ApplicationRepository
{
    /**
     * @return iterable<Application>
     */
    public function all(): iterable;

    public function create(Application $application): Application;

    public function byId(ApplicationId $id): ?Application;

    public function byIdStrict(ApplicationId $id): Application;

    public function update(Application $application): Application;
}
