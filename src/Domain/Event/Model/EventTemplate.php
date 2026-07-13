<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

interface EventTemplate
{
    public static function id(): EventId;

    public static function name(): EventName;

    public static function eventProperties(): EventProperties;

    public static function isSystemEvent(): bool;

    public static function tombstoneAfterNSeconds(): int;
}
