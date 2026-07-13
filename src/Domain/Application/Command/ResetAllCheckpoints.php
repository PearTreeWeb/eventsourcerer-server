<?php

declare(strict_types=1);

namespace App\Domain\Application\Command;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

final readonly class ResetAllCheckpoints
{
    public function __construct(public ApplicationId $id) {}

    public static function for(ApplicationId $id): self
    {
        return new self($id);
    }
}
