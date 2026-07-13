<?php

namespace App\Domain\Common\Handler;

use App\Domain\Common\Command\LogRuntimeError;
use App\Domain\Common\Repository\RuntimeErrorRepository;
use App\Domain\Common\Service\EventBroadcaster;
use App\Entity\RuntimeError;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class LogRuntimeErrorHandler
{
    public function __construct(
        private RuntimeErrorRepository $runtimeErrorRepository,
        private EventBroadcaster $eventBroadcaster,
        private ClockInterface $clock,
    ) {}

    public function __invoke(LogRuntimeError $error): void
    {
        $this->runtimeErrorRepository->create(
            RuntimeError::create(
                $error->systemError,
                $this->clock->now(),
            )
        );

        $this->eventBroadcaster->broadcastSync($error->systemError);
    }
}