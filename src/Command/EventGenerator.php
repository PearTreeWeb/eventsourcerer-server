<?php

declare(strict_types=1);

namespace App\Command;

use ApiPlatform\Metadata\Post;
use App\Domain\Common\Model\DateTimeType;
use App\Domain\Event\Query\GetAllNonSystemEvents;
use App\Entity\Event;
use App\Extension\Default\PropertyType\Integer;
use App\Extension\Default\PropertyType\Text;
use App\Extension\Default\PropertyType\UUID;
use App\Extension\Packages\Geo\PropertyType\LatAndLong;
use App\Infrastructure\QueryBus;
use App\Processor\StreamEventProcessor;
use App\Repository\Postgres\PostgresEventWriterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\ResetInterface;

#[AsCommand('app:generate:events')]
final readonly class EventGenerator
{
    private const int DEFAULT_NUMBER_OF_EVENTS_TO_PRODUCE = 100;

    private const array MOCK_STREAM_IDS  = [
        'ecb70a93-81f7-4a0a-b654-9528172e23ae',
        '99760862-e5fa-4584-9447-3ee3433e5c0d',
        'fe4fe11b-a07a-4c1f-9530-08d497d6f9d3',
    ];

    private const string LOREM_IPSUM_TEXT = <<<EOL
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pulvinar erat nec justo porttitor pharetra. Curabitur malesuada volutpat blandit. Donec eu pretium eros. Proin eu laoreet massa. Pellentesque quis neque non mauris luctus congue quis sit amet ex. Mauris id nulla vitae tellus cursus pellentesque quis vel lectus. Fusce ut maximus massa. Morbi dignissim sem non nisi consectetur pretium. Duis iaculis, nisl sed varius suscipit, urna velit convallis odio, vel bibendum elit metus id mi. Donec condimentum feugiat ligula eget pharetra. Morbi ultricies mi eget elit laoreet aliquet a id neque. Sed ultricies ex et nisl elementum, in mollis dui vehicula.
        Mauris imperdiet odio finibus, ullamcorper lorem et, accumsan tortor. Nam posuere enim ut nulla dictum interdum. Proin sapien dolor, sollicitudin sed luctus a, sodales a ex. Pellentesque id dignissim libero. Aliquam consectetur molestie quam nec efficitur. Nunc porta eros arcu, maximus facilisis enim viverra id. Maecenas in lorem hendrerit, molestie diam quis, lacinia dui. Donec sodales felis id nisi posuere mattis. Donec ac eros vitae magna eleifend vehicula. In mollis varius augue, non scelerisque odio maximus ornare. Nam malesuada id magna eu scelerisque. Quisque pretium elit id enim sagittis, et auctor tortor pharetra. Praesent eget ornare ipsum. Duis viverra tristique libero, vitae luctus enim lacinia non.
        Vestibulum pretium quam vel venenatis interdum. Donec felis tortor, egestas ac sapien eget, faucibus elementum turpis. Suspendisse congue tincidunt magna in tempus. Nam porttitor ligula id eros volutpat pellentesque. Sed tempor convallis nulla suscipit mollis. Aliquam at mi vitae lectus mollis sollicitudin vel sit amet libero. Etiam et lacinia odio, eu aliquam magna. Curabitur non nisi luctus, lacinia magna eget, auctor mauris. Nullam dui urna, ullamcorper a ante facilisis, commodo tincidunt felis. Pellentesque ipsum orci, rhoncus et ultrices et, pharetra sed lacus. Duis leo sem, mattis nec tellus ac, convallis molestie dui. Sed quis odio venenatis, porttitor sapien molestie, semper nisi. Nunc lacinia nunc vel quam consectetur, in congue ligula pellentesque. Ut et nisi laoreet, luctus nisi in, maximus magna. Nulla commodo porttitor orci vel commodo.
        Etiam purus lorem, ornare vitae fermentum eu, convallis sit amet ligula. Etiam sit amet varius mauris. Nam faucibus id lectus eu consequat. Ut vel mauris dictum, consequat dolor vitae, cursus felis. Sed dapibus dignissim vestibulum. Pellentesque efficitur, leo vel vestibulum euismod, arcu quam egestas turpis, id ornare orci ligula aliquam turpis. Duis euismod ex ut sagittis condimentum. Nullam eu viverra tortor. Maecenas pretium tempus congue. Praesent et justo interdum, aliquam ipsum sed, feugiat velit. Proin consectetur risus ut orci tempus elementum. Ut vitae nisi at urna scelerisque vehicula. Etiam magna nisl, suscipit eget dui id, laoreet malesuada mauris. Maecenas feugiat auctor pharetra.
        Nullam luctus lacinia risus, eu finibus neque interdum nec. Proin vehicula lectus dui, eu tincidunt enim scelerisque quis. Pellentesque eget erat a quam ultricies feugiat. Vestibulum ante libero, varius ac vestibulum id, vehicula at purus. Etiam nec varius dolor. Phasellus et lorem nulla. Pellentesque tincidunt lorem ex. Fusce erat massa, imperdiet in tellus sed, blandit tristique enim. In at semper odio.
    EOL;

    public function __construct(
        private QueryBus $queryBus,
        private StreamEventProcessor $streamEventProcessor,
        private EntityManagerInterface $entityManager,
        private ResetInterface $servicesResetter,
        private PostgresEventWriterRepository $postgresEventRepository,
    ) {}

    public function __invoke(
        OutputInterface $output,
        #[Argument('Number of events to generate for each event type.')]
        ?int $numberOfEventsToGenerate = self::DEFAULT_NUMBER_OF_EVENTS_TO_PRODUCE
    ): int {
        $eventIds = array_map(fn (Event $e) => $e->getId(), $this->queryBus->query(new GetAllNonSystemEvents()));

        $progressBar = new ProgressBar($output, $numberOfEventsToGenerate);
        $numberOfEventsWritten = 0;

        foreach ($eventIds as $eventId) {
            for ($i = 0; $i < $numberOfEventsToGenerate; $i++) {
                $event = $this->entityManager->find(Event::class, $eventId->toUuid());

                $streamId = self::MOCK_STREAM_IDS[random_int(0, (count(self::MOCK_STREAM_IDS) -1))];
                $stream = 'mockEvents';

                $properties = [];

                foreach ($this->postgresEventRepository->eventPropertiesForEventWithId($eventId) as $property) {
                    $properties = array_merge($properties, self::createMockEventPropertyValue($property, $streamId));
                }

                $streamEvent = (object) [
                    'stream' => sprintf('%s-%s', $stream, $streamId),
                    'streamName' => $stream,
                    'streamId' => $streamId,
                    'event' => $event->getName(),
                    'version' => 1,
                    'properties' => $properties,
                    'expectedVersion' => null,
                ];

                $this->streamEventProcessor->process($streamEvent, new Post());
                $progressBar->advance();

                $numberOfEventsWritten++;

                if ($numberOfEventsWritten % 100 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $this->servicesResetter->reset();
                }
            }
        }

        $progressBar->finish();
        $output->writeln(PHP_EOL . '<info>Generated events</info>');

        return Command::SUCCESS;
    }

    /**
     * @param array{type_class: string, name: string}  $eventProperty
     *
     * @return array|\DateTimeImmutable[]|string[]
     */
    private static function createMockEventPropertyValue(array $eventProperty, string $streamId): array
    {
        return match ($eventProperty['type_class']) {
            UUID::class => self::createMockUUIDValue($eventProperty['name'], $streamId),
            Integer::class => self::createMockIntegerValue($eventProperty['name']),
            Text::class => self::createMockTextValue($eventProperty['name']),
            DateTimeType::class => self::createMockDateTimeValue($eventProperty['name']),
            LatAndLong::class => self::createMockLatLongValue($eventProperty['name']),
        };
    }

    /**
     * @return array<string, string>
     */
    private static function createMockUUIDValue(string $name, string $streamId): array
    {
        return [$name => $streamId];
    }

    /**
     * @return array<string, int>
     */
    private static function createMockIntegerValue(string $name): array
    {
        return [$name => random_int(0, 1000)];
    }

    /**
     * @return array<string, string>
     */
    private static function createMockTextValue(string $name): array
    {
        return [$name => mb_trim(self::LOREM_IPSUM_TEXT)];
    }

    /**
     * @return array<string, \DateTimeImmutable>
     */
    private static function createMockDateTimeValue(string $name): array
    {
        return [$name => new \DateTimeImmutable()];
    }


    /**
     * @return array<string, string>
     */
    private static function createMockLatLongValue(string $name): array
    {
        return [
            $name => (random_int(-900000, 900000) / 10000) . ', ' . (random_int(-1800000, 1800000) / 10000)
        ];
    }
}
