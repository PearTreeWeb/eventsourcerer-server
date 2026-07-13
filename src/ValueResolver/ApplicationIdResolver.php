<?php

declare(strict_types=1);

namespace App\ValueResolver;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ApplicationIdResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: ApplicationId}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType !== ApplicationId::class) {
            return [];
        }

        return [ApplicationId::fromString($request->attributes->get($argument->getName()))];
    }
}
