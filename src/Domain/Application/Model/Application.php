<?php

namespace App\Domain\Application\Model;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

interface Application
{
    public static function id(): ApplicationId;
    public static function name(): ApplicationName;
}
