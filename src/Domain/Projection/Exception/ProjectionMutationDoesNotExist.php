<?php

namespace App\Domain\Projection\Exception;

use App\Domain\Projection\Model\ProjectionMutationId;

final class ProjectionMutationDoesNotExist extends \RuntimeException
{
    public static function withId(ProjectionMutationId $id): self
    {
        return new self(
            sprintf(
                'Projection mutation with id "%s" does not exist', $id
            )
        );
    }
}