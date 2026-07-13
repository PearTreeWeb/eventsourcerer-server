<?php

declare(strict_types=1);

namespace App\Domain\Widget\Handler;

use App\Domain\Widget\Command\RegisterWidget;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RegisterWidgetHandler
{
    public function __invoke(RegisterWidget $command): void
    {
    }
}
