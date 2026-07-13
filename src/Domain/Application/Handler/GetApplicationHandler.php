<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Query\GetApplication;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Entity\Application;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetApplicationHandler
{
    public function __construct(private ApplicationRepository $applicationRepository) {}

    public function __invoke(GetApplication $query): Application
    {
        return $this->applicationRepository->byIdStrict($query->id);
    }
}
