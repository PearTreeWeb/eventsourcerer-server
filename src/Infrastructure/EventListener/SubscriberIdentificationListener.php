<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Identifies the subscriber from the request (e.g., subdomain)
 * and sets the SUBSCRIBER_ID in $_SERVER and $_ENV.
 * 
 * This must run very early (before Doctrine connects or the Kernel builds the container
 * if possible, though container building for the current request happened already,
 * but this affects subsequent service calls).
 */
final class SubscriberIdentificationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            // High priority to run before most other listeners
            KernelEvents::REQUEST => ['onKernelRequest', 255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $host = $request->getHost();

        // Example: Identifying via subdomain (e.g., subscriber1.example.com)
        // You can also use a custom header like 'X-Subscriber-Id'
        $subscriberId = null;

        // Check for Header first (useful for API)
        if ($request->headers->has('X-Subscriber-Id')) {
            $subscriberId = $request->headers->get('X-Subscriber-Id');
        } else {
            // Otherwise, try to extract from subdomain
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $subscriberId = $parts[0];
            }
        }

        if ($subscriberId && preg_match('/^[a-zA-Z0-9_]+$/', $subscriberId)) {
            $_SERVER['SUBSCRIBER_ID'] = $subscriberId;
            $_ENV['SUBSCRIBER_ID'] = $subscriberId;
            
            // Also store it in request attributes for easy access elsewhere
            $request->attributes->set('subscriber_id', $subscriberId);
        }
    }
}
