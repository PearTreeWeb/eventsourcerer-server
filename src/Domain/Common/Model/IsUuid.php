<?php

namespace App\Domain\Common\Model;

use App\Domain\Common\HasKey;
use Symfony\Component\Uid\Uuid;

interface IsUuid extends HasKey
{
    public static function fromString(string $value): self;

    public function toUuid(): Uuid;
}
