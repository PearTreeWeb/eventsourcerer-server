<?php

declare(strict_types=1);

namespace App\Tests\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Author\Model\AuthorId;
use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\Event\Model\EventId;
use App\Entity\Author;
use App\Entity\Event;
use App\State\EventCollectionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class EventCollectionProviderTest extends TestCase
{
    public function test_it_adds_the_author_name_to_each_event(): void
    {
        $authorId = AuthorId::fromUuid(Uuid::v4());
        $author   = Author::create($authorId, 'Jane Doe');

        $event = Event::create(
            EventId::fromUuid(Uuid::v4()),
            'user.registered',
            0,
            new \DateTimeImmutable(),
            $authorId->toUuid(),
        );

        $decoratedProvider = $this->createMock(ProviderInterface::class);
        $decoratedProvider
            ->method('provide')
            ->willReturn([$event]);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository
            ->method('all')
            ->willReturn([$author]);

        $provider = new EventCollectionProvider($decoratedProvider, $authorRepository);

        $result = $provider->provide(new GetCollection(class: Event::class));

        self::assertSame('Jane Doe', $result[0]->getAuthorName());
    }

    public function test_it_leaves_the_author_name_null_when_event_has_no_author(): void
    {
        $event = Event::create(
            EventId::fromUuid(Uuid::v4()),
            'user.registered',
            0,
            new \DateTimeImmutable(),
        );

        $decoratedProvider = $this->createMock(ProviderInterface::class);
        $decoratedProvider
            ->method('provide')
            ->willReturn([$event]);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository
            ->method('all')
            ->willReturn([]);

        $provider = new EventCollectionProvider($decoratedProvider, $authorRepository);

        $result = $provider->provide(new GetCollection(class: Event::class));

        self::assertNull($result[0]->getAuthorName());
    }
}
