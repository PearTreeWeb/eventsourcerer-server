<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\CatchupManager;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: self::COMMAND)]
final readonly class StartCatchupProcess
{
    public const string COMMAND = 'app:catchup:start';

    public function __construct(
        private CatchupManager $catchupManager,
    ) {}

    public function __invoke(
        #[Argument] string $applicationId,
        #[Argument] string $streamId,
        #[Argument] string $workerId
    ): int {
        $this->catchupManager->startFor(
            ApplicationId::fromString($applicationId),
            StreamId::fromString($streamId),
            WorkerId::fromString($workerId)
        );

        return Command::SUCCESS;
    }
}
