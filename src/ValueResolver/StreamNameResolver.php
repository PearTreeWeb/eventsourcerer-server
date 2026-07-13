<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Domain\Event\Model\StreamName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class StreamNameResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: StreamName}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType !== StreamName::class) {
            return [];
        }

        return [StreamName::fromString($request->attributes->get($argument->getName()))];
    }
}
