<?php

declare(strict_types=1);

namespace App\Extension\Packages\MicroManager\Author;

use App\Domain\Author\AuthorDetails;
use App\Domain\Author\Model\AuthorId;
use App\Domain\Common\Model\Author;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.author_template')]
final readonly class MicroManager implements AuthorDetails
{
    private const string AUTHOR_ID = 'd267414d-6d56-44fc-8380-615e0f2a48e5';
    private const string AUTHOR_NAME = 'MicroManager';

    public static function author(): Author
    {
        return Author::fromString(self::AUTHOR_NAME);
    }

    public static function id(): AuthorId
    {
        return AuthorId::fromString(self::AUTHOR_ID);
    }
}
