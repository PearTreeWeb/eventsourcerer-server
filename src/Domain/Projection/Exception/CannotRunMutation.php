<?php

namespace App\Domain\Projection\Exception;

use App\Domain\Projection\Model\ProjectionId;

final class CannotRunMutation extends \RuntimeException
{
    public static function becauseAResetIsHappeningCurrently(ProjectionId $projectionId): self
    {
        return new self(
            sprintf(
                'Cannot run mutation on projection with ID "%s" because a reset is currently happening',
                $projectionId
            )
        );
    }
}