<?php

declare(strict_types=1);

namespace App\ValueResolver;

use App\Domain\Common\Model\IsUuid;
use App\Domain\Common\Model\UniqueIdentifier;
use App\Domain\Event\Model\StreamName;
use App\Domain\Widget\Model\WidgetName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UuidResolver implements ValueResolverInterface
{
    /**
     * @return array{0?: IsUuid}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ('int' === $argumentType || 'string' === $argumentType || null === $argumentType) {
            return [];
        }

        if (null === ($class = $argument->getType())) {
            return [];
        }

        if (!is_a($class, IsUuid::class, true)) {
            return [];
        }

        return [$class::fromString($request->attributes->get($argument->getName()))];
    }
}
