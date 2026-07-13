<?php

namespace App\Domain\Common\Command;

use App\Domain\Common\Model\SystemError;

final readonly class LogRuntimeError
{
    public function __construct(public SystemError $systemError) {}
}