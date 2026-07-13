<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\CanBeRepresentedAsArray;
use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsString;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;

final class EventPropertyValue implements CanBeRepresentedAsArray, IsString
{
    use FulfilIsString;

    /**
     * @throws \JsonException
     */
    public function toArray(): array
    {
        $decoded = json_decode($this->value, true, 512, JSON_THROW_ON_ERROR);

        return false === $decoded
            ? [$this->value]
            : $decoded;
    }
}
