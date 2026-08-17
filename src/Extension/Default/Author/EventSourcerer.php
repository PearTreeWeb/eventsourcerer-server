<?php

declare(strict_types=1);

namespace App\Extension\Default\Author;

use App\Domain\Author\AuthorDetails;
use App\Domain\Author\Model\AuthorId;
use App\Domain\Common\Model\Author;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.author_template')]
final readonly class EventSourcerer implements AuthorDetails
{
    private const string AUTHOR_ID = '1035eecc-0506-4e72-88cd-76775c7803be';

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function id(): AuthorId
    {
        return AuthorId::fromString(self::AUTHOR_ID);
    }
}
