<?php

declare(strict_types=1);

namespace App\ValueResolver;

use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class StreamIdResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: StreamId}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType !== StreamId::class) {
            return [];
        }

        return [StreamId::fromString($request->attributes->get($argument->getName()))];
    }
}
