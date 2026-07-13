<?php

namespace App\Domain\Common\Repository;

use App\Entity\RuntimeError;

interface RuntimeErrorRepository
{
    public function create(RuntimeError $error): void;

    /**
     * @return iterable<RuntimeError>
     */
    public function paginated(int $start, int $perPage): iterable;
}