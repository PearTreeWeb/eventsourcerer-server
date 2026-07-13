<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\Model\FulfilIsUuid;
use App\Domain\Common\Model\IsUuid;

final class EventPropertyId implements IsUuid
{
    use FulfilIsUuid;

    private const string METADATA_ID = 'a2b461d0-794c-4e6c-8f9c-8d734ec10eb4';

    public static function metadata(): self
    {
        return self::fromString(self::METADATA_ID);
    }

    public function isMetadata(): bool
    {
        return $this->toUuid()->equals(self::metadata()->toUuid());
    }
}
