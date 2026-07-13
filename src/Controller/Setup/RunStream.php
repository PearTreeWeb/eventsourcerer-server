<?php

declare(strict_types=1);

namespace App\Controller\Setup;

use App\Setup\InstallStateService;
use App\Setup\Step\SetupStepInterface;
use App\Setup\Step\SetupStepResult;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/setup/run', name: 'setup_run')]
final class RunStream extends AbstractController
{
    /**
     * @param iterable<SetupStepInterface> $steps
     */
    public function __construct(
        private readonly iterable $steps,
        private readonly InstallStateService $installState,
    ) {}

    public function __invoke(LoggerInterface $logger, Request $request): Response
    {
        $session = $request->hasSession() ? $request->getSession() : null;
        $sessionId = $session?->getId() ?? 'anonymous';
        $logger->info('Setup stream started', ['sessionId' => $sessionId]);
        $steps = $this->steps;
        $progressFile = $this->getParameter('kernel.project_dir') . '/var/setup_progress_' . $sessionId . '.json';

        $completedSteps = [];
        if (file_exists($progressFile)) {
            $completedSteps = json_decode(file_get_contents($progressFile), true) ?? [];
        }

        $installState = $this->installState;
        $response = new EventStreamResponse(function () use ($steps, $logger, $request, &$completedSteps, $progressFile, $installState) {
            $session = $request->hasSession() ? $request->getSession() : null;
            // Disable time limit for long-running setup
            set_time_limit(0);

            // Send initial comment to kickstart the stream and bypass some proxy buffering
            echo ": ok\n\n";
            // Also send a retry directive to the client
            echo "retry: 2000\n\n";
            
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            flush();

            // Disable output buffering to ensure events are sent immediately
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            try {
                foreach ($steps as $step) {
                    $label = $step->label();
                    
                    if (in_array($label, $completedSteps, true)) {
                        $logger->info('Skipping already completed setup step: ' . $label);
                        yield new ServerEvent(
                            json_encode([
                                'label'   => $label,
                                'status'  => 'success',
                                'message' => 'Complete',
                            ], JSON_THROW_ON_ERROR),
                            type: 'step',
                        );
                        continue;
                    }

                    $logger->info('Running setup step: ' . $label);
                    
                    // Explicitly set retry to 1 second
                    yield new ServerEvent(
                        json_encode([
                            'label'   => $label,
                            'status'  => 'running',
                            'message' => null,
                        ], JSON_THROW_ON_ERROR),
                        type: 'step',
                        retry: 2000,
                    );

                    // Force flush after each yield to ensure client receives it
                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();

                    // Sleep for 10ms to give the browser time to breathe and process the 'running' state
                    usleep(10000);

                    // Keep the connection alive by sending a heartbeat
                    yield new ServerEvent('', type: 'heartbeat');

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();

                    try {
                        $logger->info('Executing step run: ' . $step->label());
                        $result = $step->run($request);
                    } catch (\Throwable $e) {
                        $logger->error('Setup step threw exception: ' . $e->getMessage());
                        $result = SetupStepResult::failure($e->getMessage());
                    }

                    $logger->info('Setup step result: ' . ($result->success ? 'success' : 'failure'));

                    if ($result->success) {
                        $completedSteps[] = $label;
                        try {
                            file_put_contents($progressFile, json_encode($completedSteps, JSON_THROW_ON_ERROR));
                        } catch (\Throwable $progressError) {
                            $logger->warning('Could not update progress in file: ' . $progressError->getMessage());
                        }
                    }

                    yield new ServerEvent(
                        json_encode([
                            'label'   => $label,
                            'status'  => $result->success ? 'success' : 'failure',
                            'message' => $result->message,
                        ], JSON_THROW_ON_ERROR),
                        type: 'step',
                    );

                    // Force flush
                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();

                    if (!$result->success) {
                        yield new ServerEvent('failure', type: 'done');
                        usleep(500000); // 500ms

                        return;
                    }
                }
            } catch (\Throwable $e) {
                $logger->error('Fatal error in setup stream: ' . $e->getMessage());
                yield new ServerEvent(
                    json_encode([
                        'label'   => 'Fatal Error',
                        'status'  => 'failure',
                        'message' => $e->getMessage(),
                    ], JSON_THROW_ON_ERROR),
                    type: 'step',
                );
            }

            // All steps succeeded — mark the application as installed.
            $installState->markInstalled();
            $logger->info('Application marked as installed');

            // Sleep 100ms to allow file system to sync
            usleep(100000);

            yield new ServerEvent('success', type: 'done');

            $logger->info('Setup stream finished successfully');

            // Wait a bit longer to ensure the client processes the 'done' event 
            // before the connection is physically closed.
            usleep(500000); // 500ms
            
            // Final flush
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            flush();
        });

        // We DO NOT save/close the session here anymore, because we need it to remain 
        // open (and locked) during the stream if we want to write to it.
        // Symfony's NativeSessionStorage might try to send headers if we start it 
        // after output has begun. By keeping it open, we avoid that.
        // NOTE: This might cause other requests from the same user to hang 
        // until the setup is done, but since this is a one-time setup page, 
        // it is acceptable.

        return $response;
    }
}
