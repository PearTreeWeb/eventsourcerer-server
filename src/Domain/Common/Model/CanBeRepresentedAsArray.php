<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

interface CanBeRepresentedAsArray
{
    public function toArray(): array;
}
