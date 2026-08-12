<?php

declare(strict_types=1);

namespace App\Domain\Author\Repository;

use App\Entity\Author;

interface AuthorRepository
{
    public function findByName(string $name): ?Author;

    public function create(Author $author): Author;

    /**
     * @return list<Author>
     */
    public function all(): array;
}
