<?php

namespace App\Domain\Common\Model;

interface CanBeCreatedFromArray
{
    public static function fromArray(array $item): self;
}
