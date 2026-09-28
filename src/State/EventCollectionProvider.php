<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Author\Model\AuthorId;
use App\Domain\Author\Repository\AuthorRepository;
use App\Entity\Author;
use App\Entity\Event;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Event>
 */
final readonly class EventCollectionProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        private AuthorRepository $authorRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $events = $this->collectionProvider->provide($operation, $uriVariables, $context);

        /** @var array<string, Author> $authors */
        $authors = [];
        foreach ($this->authorRepository->all() as $author) {
            $authors[$author->getId()->toRfc4122()] = $author;
        }

        foreach ($events as $event) {
            if (!$event instanceof Event) {
                continue;
            }

            $authorId = $event->getAuthorId();

            if ($authorId === null) {
                $event->setAuthorName('Unknown');

                continue;
            }

            $author = $authors[$authorId->toRfc4122()] ?? $this->authorRepository->findById(AuthorId::fromUuid($authorId));

            $event->setAuthorName($author?->getName());
        }

        return $events;
    }
}
