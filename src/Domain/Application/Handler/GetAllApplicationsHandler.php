<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Query\GetAllApplications;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Entity\Application;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllApplicationsHandler
{
    public function __construct(private ApplicationRepository $repository) {}

    /**
     * @return iterable<Application>
     */
    public function __invoke(GetAllApplications $query): iterable
    {
        return $this->repository->all();
    }
}
