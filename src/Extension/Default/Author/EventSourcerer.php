<?php

declare(strict_types=1);

namespace App\Extension\Default\Author;

use App\Domain\Author\AuthorDetails;
use App\Domain\Common\Model\Author;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.author_template')]
final class EventSourcerer implements AuthorDetails
{
    public static function author(): Author
    {
        return Author::eventSourcerer();
    }
}
