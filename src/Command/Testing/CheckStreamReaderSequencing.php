<?php

namespace App\Command\Testing;

use League\Flysystem\FilesystemReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:testing:check-stream-reader-sequencing')]
final readonly class CheckStreamReaderSequencing
{
    private const string SOCKET_LOG_FILE = 'socket.log';

    public function __construct(private FilesystemReader $systemLogs) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $resource = $this->systemLogs->readStream(self::SOCKET_LOG_FILE);

        $incorrectlySequenced = self::invalid($resource);
        $invalidEntriesFound = false;

        foreach ($incorrectlySequenced as $incorrect) {
            $invalidEntriesFound = true;

            $io->caution($incorrect);
        }

        if (!$invalidEntriesFound) {
            $io->success('No invalid entries found');
        }

        return Command::SUCCESS;
    }

    /**
     * @return iterable<string>
     */
    private static function invalid(mixed $resource): iterable
    {
        $lastStream = null;
        $lastStreamSeq = null;
        $currentWorkerPerStream = [];

        while (!feof($resource)) {
            $line = fgets($resource);

            if (str_contains($line, 'Forwarded event')) {
                $lineParts = explode(' ', $line);

                $stream = str_replace('stream=', '', $lineParts[4]);
                $streamSeq = (int) str_replace('seq=', '', $lineParts[5]);
                $workerId = $lineParts[9];

                if (isset($currentWorkerPerStream[$stream])) {
                    $currentWorker = $currentWorkerPerStream[$stream]['workerId'];
                    $currentSeq = $currentWorkerPerStream[$stream]['seq'];

                    if ($currentWorker !== $workerId && $streamSeq !== 1) {
                        yield sprintf(
                            'Stream "%s" read by multiple workers concurrently. Worker "%s" was at seq %d when worker "%s" read seq %d',
                            $stream,
                            $currentWorker,
                            $currentSeq,
                            $workerId,
                            $streamSeq,
                        );
                    }
                }

                $currentWorkerPerStream[$stream] = ['workerId' => $workerId, 'seq' => $streamSeq];

                if ($stream === $lastStream && ($lastStreamSeq + 1) !== $streamSeq) {
                    yield sprintf(
                        'Incorrectly sequenced event: %s %d',
                        $stream,
                        $streamSeq
                    );
                }

                $lastStream = $stream;
                $lastStreamSeq = $streamSeq;
            }
        }
    }
}
