<?php

namespace App\Domain\Common\Handler;

use App\Domain\Common\Query\GetRuntimeErrors;
use App\Domain\Common\Repository\RuntimeErrorRepository;
use App\Entity\RuntimeError;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetRuntimeErrorsHandler
{
    public function __construct(private RuntimeErrorRepository $repository) {}

    /**
     * @return iterable<RuntimeError>
     */
    public function __invoke(GetRuntimeErrors $query): iterable
    {
        return $this->repository->paginated($query->start, $query->perPage);
    }
}