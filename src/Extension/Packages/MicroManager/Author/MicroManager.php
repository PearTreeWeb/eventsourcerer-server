<?php

declare(strict_types=1);

namespace App\Extension\Packages\MicroManager\Author;

use App\Domain\Author\AuthorDetails;
use App\Domain\Common\Model\Author;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.author_template')]
final readonly class MicroManager implements AuthorDetails
{
    private const string AUTHOR_NAME = 'MicroManager';

    public static function author(): Author
    {
        return Author::fromString(self::AUTHOR_NAME);
    }
}
