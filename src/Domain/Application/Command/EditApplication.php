<?php

declare(strict_types=1);

namespace App\Domain\Application\Command;

use App\Domain\Application\Model\ApplicationName;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

final readonly class EditApplication
{
    public function __construct(
        public ApplicationId $id,
        public ApplicationName $name,
        public ?string $hostname = null
    ) {}
}
