<?php

declare(strict_types=1);

namespace App\Domain\Widget\Handler;

use App\Domain\Widget\Query\GetAllRegisteredProjections;
use App\Repository\WidgetRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllRegisteredProjectionsHandler
{
    public function __construct(private WidgetRepository $widgetRepository) {}

    /**
     * @return iterable<string>
     */
    public function __invoke(GetAllRegisteredProjections $query): iterable
    {
        return $this->widgetRepository->allRegisteredProjections();
    }
}
