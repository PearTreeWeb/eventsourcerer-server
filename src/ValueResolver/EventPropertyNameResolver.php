<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Domain\Event\Model\EventPropertyName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class EventPropertyNameResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: EventPropertyName}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return EventPropertyName::class === $argument->getType()
            ? [EventPropertyName::fromString($request->attributes->get($argument->getName()))]
            : [];
    }
}
