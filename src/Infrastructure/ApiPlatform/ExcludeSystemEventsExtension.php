<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event;
use Doctrine\ORM\QueryBuilder;

/**
 * Ensures system events are filtered out by default from GetCollection operation.
 */
final class ExcludeSystemEventsExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== Event::class) {
            return;
        }

        $rootAliases = $queryBuilder->getRootAliases();
        $rootAlias = $rootAliases[0] ?? 'o';

        $queryBuilder
            ->andWhere(sprintf('%s.systemEvent = :systemEvent', $rootAlias))
            ->setParameter('systemEvent', false);

        // Allow overriding via query parameter if BooleanFilter is present
        $filters = $context['filters'] ?? [];
        if (isset($filters['systemEvent'])) {
            $value = filter_var($filters['systemEvent'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($value !== null) {
                $queryBuilder->setParameter('systemEvent', $value);
            }
        }
    }
}
