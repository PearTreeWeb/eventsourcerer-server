<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Setup\InstallStateService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class SetupRedirectSubscriber implements EventSubscriberInterface
{
    /**
     * Path prefixes that are allowed even when the application is not installed.
     */
    private const array ALLOWED_PREFIXES = [
        '/setup',
        '/login',
        '/logout',
        '/2fa',
        '/2fa_check',
        '/api',
        '/_wdt',
        '/_profiler',
        '/_error',
        '/css',
        '/js',
        '/images',
        '/build',
        '/assets',
        '/favicon',
    ];

    public function __construct(
        private InstallStateService $installState,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Must run before Symfony's RouterListener resolves the route, but after firewall.
            KernelEvents::REQUEST => ['onKernelRequest', 28],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return;
            }
        }

        if ($this->installState->isInstalled()) {
            return;
        }

        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate('setup'))
        );
    }
}
