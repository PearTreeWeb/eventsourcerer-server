<?php

namespace App\Domain\Common\Model;

interface SystemError extends \Stringable
{
    public function message(): ErrorMessage;
}