<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Author\Model\AuthorId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'author')]
class Author
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true)]
    private string $name;

    public static function create(AuthorId $id, string $name): self
    {
        $author = new self();
        $author->id = $id->toUuid();
        $author->name = $name;

        return $author;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
