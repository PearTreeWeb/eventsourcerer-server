<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

final class AuthorImageExtension extends AbstractExtension
{
    private const array IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

    /** @var array<string, string|null> */
    private array $imageCache = [];

    /** @var array<string, string> */
    private array $nameCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $projectDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('author_image', $this->renderAuthorImage(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderAuthorImage(string|Uuid|null $authorId, string $cssClass = 'w-8 h-8 rounded-full object-cover'): Markup
    {
        if ($authorId === null) {
            return new Markup('', 'UTF-8');
        }

        $id = $authorId instanceof Uuid ? $authorId->toRfc4122() : $authorId;

        $relative = $this->findImage($id);
        if ($relative !== null) {
            $name = htmlspecialchars($this->getAuthorName($id) ?? '', ENT_QUOTES, 'UTF-8');
            $src  = htmlspecialchars($relative, ENT_QUOTES, 'UTF-8');
            $cls  = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');

            return new Markup(sprintf('<img src="%s" alt="%s" title="%s" class="%s" />', $src, $name, $name, $cls), 'UTF-8');
        }

        $name = $this->getAuthorName($id) ?? $id;

        return new Markup(htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), 'UTF-8');
    }

    private function findImage(string $authorId): ?string
    {
        if (array_key_exists($authorId, $this->imageCache)) {
            return $this->imageCache[$authorId];
        }

        $dir = $this->projectDir . '/public/images/authors';
        foreach (self::IMAGE_EXTENSIONS as $ext) {
            $path = $dir . '/' . $authorId . '.' . $ext;
            if (is_file($path)) {
                return $this->imageCache[$authorId] = '/images/authors/' . $authorId . '.' . $ext;
            }
        }

        return $this->imageCache[$authorId] = null;
    }

    private function getAuthorName(string $authorId): ?string
    {
        if (array_key_exists($authorId, $this->nameCache)) {
            return $this->nameCache[$authorId];
        }

        try {
            $uuid = Uuid::fromString($authorId);
        } catch (\InvalidArgumentException) {
            return $this->nameCache[$authorId] = null;
        }

        $author = $this->entityManager->getRepository(Author::class)->find($uuid);

        return $this->nameCache[$authorId] = $author?->getName();
    }
}
