<?php

declare(strict_types=1);

namespace App\Command\Testing;

use App\Repository\Postgres\PostgresStreamEventRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand(name: 'app:testing:test')]
final readonly class Test
{
    public function __construct(private PostgresStreamEventRepository $streamEventRepository) {}

    public function __invoke(): int
    {
        $this->streamEventRepository->oldestUnworkedEvent(
            ApplicationId::fromString('5c285f4a-0a0c-5c4e-8936-66be1ff68ad7'),
            [],
            Checkpoint::zero(),
        );

        return Command::SUCCESS;
    }
}
