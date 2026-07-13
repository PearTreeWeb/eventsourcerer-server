<?php

declare(strict_types=1);

namespace App\Extension\Default\Widget;

use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Query\GetProjectionMasterStateByName;
use App\Domain\Stream\Query\GetStreamEvents;
use App\Domain\Widget\Model\WidgetName;
use App\Entity\StreamEvent;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Twig\Environment;

abstract readonly class AbstractWidget
{
    private const string TEMPLATE_FILE = 'main.html.twig';
    private const int DEFAULT_PAGINATION_START = 0;
    private const int DEFAULT_PAGINATION_LIMIT = 10;

    public function __construct(
        private QueryBus $queryBus,
        private Environment $twigEnvironment
    ) {}

    /**
     * @return array<string, mixed>
     */
    protected function projectionState(?ProjectionName $name): array
    {
        if (null === $name) {
            return [];
        }

        return $this
            ->queryBus
            ->query(new GetProjectionMasterStateByName($name))
            ?->getCurrentState() ?? [];
    }

    /**
     * @return iterable<StreamEvent>
     */
    protected function events(): iterable
    {
        $streamId = $this->streamId();

        if (null === $streamId) {
            return [];
        }

        return $this->queryBus->query(
            GetStreamEvents::withStreamId($streamId,
                self::DEFAULT_PAGINATION_START,
                self::DEFAULT_PAGINATION_LIMIT
            )
        );
    }

    abstract protected function projectionName(): ?ProjectionName;

    abstract protected function streamId(): ?StreamId;

    abstract public static function name(): WidgetName;

    public function view(): string
    {
        return $this->twigEnvironment->render(
            $this->folder() . self::TEMPLATE_FILE,
            [
                'name' => static::name()->toString(),
                'events' => $this->events(),
                'state' => $this->projectionState($this->projectionName()),
            ]
        );
    }

    private function folder(): string
    {
        return sprintf(
            '%s/Widget/templates/',
            str_replace(' ', '', $this::name()->toString()),
        );
    }
}
