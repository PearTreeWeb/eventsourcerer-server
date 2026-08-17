<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\Author;

interface EventTemplate
{
    public static function id(): EventId;

    public static function name(): EventName;

    public static function eventProperties(): EventProperties;

    public static function isSystemEvent(): bool;

    public static function tombstoneAfterNSeconds(): int;

    public static function author(): Author;
}
