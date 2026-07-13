<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Projection;
use App\Entity\ProjectionState;
use Doctrine\ORM\QueryBuilder;

/**
 * Ensures ProjectionState is only exposed via API when the parent Projection has exposeStateViaApi = true.
 */
final class ProjectionStateExposureExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== ProjectionState::class) {
            return;
        }

        $this->restrictToExposedProjections($queryBuilder);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($resourceClass !== ProjectionState::class) {
            return;
        }

        $this->restrictToExposedProjections($queryBuilder);
    }

    private function restrictToExposedProjections(QueryBuilder $queryBuilder): void
    {
        $rootAliases = $queryBuilder->getRootAliases();
        $rootAlias = $rootAliases[0] ?? 'o';

        // Only return states whose parent projection has exposeStateViaApi = true
        $queryBuilder
            ->andWhere(sprintf(
                'EXISTS (SELECT 1 FROM %s p WHERE p.id = %s.projectionId AND p.exposeStateViaApi = :expose)',
                Projection::class,
                $rootAlias
            ))
            ->setParameter('expose', true);
    }
}
