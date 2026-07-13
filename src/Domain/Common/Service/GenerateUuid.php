<?php

namespace App\Domain\Common\Service;

use PearTreeWeb\EventSourcerer\Common\Model\IsString;
use Symfony\Component\Uid\Uuid;

interface GenerateUuid
{
    public function for(IsString $object): Uuid;

    public function random(): Uuid;
}
