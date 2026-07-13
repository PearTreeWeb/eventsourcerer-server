<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Domain\Widget\Model\WidgetName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class WidgetNameResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: WidgetName}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (WidgetName::class === $argument->getType()) {
            return [WidgetName::fromString($request->attributes->get($argument->getName()))];
        }

        return [];
    }
}
