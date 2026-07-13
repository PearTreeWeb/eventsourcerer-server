<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;

final class NoWorkerFound extends \RuntimeException
{
    public static function withId(WorkerId $workerId): self
    {
        return new self(
            sprintf('No worker can be found with ID "%s"', $workerId)
        );
    }
}
