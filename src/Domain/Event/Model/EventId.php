<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;
use App\Domain\Event\Repository\EventRepository;

final class EventId implements IsUuid
{
    use FulfilIsUuid;

    public static function any(): self
    {
        return self::fromString(EventRepository::ANY_EVENT_ID_REPRESENTATION);
    }

    public function isAny(): bool
    {
        return $this->sameAs(self::any());
    }
}
