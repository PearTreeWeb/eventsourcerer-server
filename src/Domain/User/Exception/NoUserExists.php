<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\User\Model\EmailAddress;

final class NoUserExists extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(
            sprintf(
                'No user exists with ID "%s"',
                $id
            )
        );
    }

    public static function withEmailAddress(EmailAddress $emailAddress): self
    {
        return new self(
            sprintf(
                'No user exists with email address "%s"',
                $emailAddress,
            )
        );
    }
}
