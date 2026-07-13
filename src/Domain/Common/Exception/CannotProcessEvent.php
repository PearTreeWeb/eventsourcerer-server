<?php

namespace App\Domain\Common\Exception;

final class CannotProcessEvent extends \RuntimeException
{
    public static function expectedVersionIsDifferent(int $serverExpectedVersion, int $clientExpectedVersion): self
    {
        return new self(
            sprintf(
                'Cannot process event because the server expects current version to be %d but the client believes current version to be %d',
                $serverExpectedVersion,
                $clientExpectedVersion,
            )
        );
    }
}