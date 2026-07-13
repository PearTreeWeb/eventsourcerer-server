<?php

declare(strict_types=1);

namespace App\Controller\Settings;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Projection\Model\CanMutate;
use App\Extension\Default\ConditionOperators\ConditionOperator;
use App\Extension\Default\Widget\Widget;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_USER')]
#[Route('/settings/packages', name: 'settings_packages', methods: ['GET'])]
final class Packages extends AbstractController
{
    /**
     * @param iterable<PropertyType> $propertyTypes
     * @param iterable<Widget> $widgets
     * @param iterable<ConditionOperator> $conditionOperators
     * @param iterable<CanMutate> $mutationTypes
     */
    public function __construct(
        private readonly iterable $propertyTypes,
        private readonly iterable $widgets,
        private readonly iterable $conditionOperators,
        private readonly iterable $mutationTypes,
    ) {}

    public function __invoke(): Response
    {
        $extensions = self::groupExtensions(
            $this->propertyTypes,
            $this->widgets,
            $this->conditionOperators,
            $this->mutationTypes,
        );

        $authors = array_keys($extensions);
        sort($authors);

        $packages = [];
        foreach ($extensions as $authorData) {
            foreach (['propertyTypes', 'widgets', 'conditionOperators', 'mutationTypes'] as $type) {
                foreach ($authorData[$type] as $item) {
                    $packages[] = $item['package'];
                }
            }
        }
        $packages = array_unique($packages);
        sort($packages);

        return $this->render(
            'UI/settings/packages.html.twig',
            [
                'extensions' => $extensions,
                'authors' => $authors,
                'packages' => $packages,
            ]
        );
    }

    /**
     * @param iterable<PropertyType> $propertyTypes
     * @param iterable<Widget> $widgets
     * @param iterable<ConditionOperator> $conditionOperators
     * @param iterable<CanMutate> $mutationTypes
     *
     * @return array<string, array<string, mixed>>
     */
    private static function groupExtensions(
        iterable $propertyTypes,
        iterable $widgets,
        iterable $conditionOperators,
        iterable $mutationTypes,
    ): array {
        $extensions = [];

        foreach ($propertyTypes as $propertyType) {
            $author = $propertyType::author()->toString();
            self::ensureAuthorInitialized($extensions, $author);
            $extensions[$author]['propertyTypes'][] = [
                'name' => $propertyType::name()->toString(),
                'class' => $propertyType::class,
                'conditionOperators' => $propertyType::conditionOperators(),
                'exampleInput' => $propertyType::exampleInput(),
                'package' => $propertyType::package()->toString(),
            ];
        }

        foreach ($widgets as $widget) {
            $author = $widget::author()->toString();
            self::ensureAuthorInitialized($extensions, $author);
            $extensions[$author]['widgets'][] = [
                'name' => $widget::name()->toString(),
                'class' => $widget::class,
                'package' => $widget::package()->toString(),
            ];
        }

        foreach ($conditionOperators as $conditionOperator) {
            $author = $conditionOperator::author()->toString();
            self::ensureAuthorInitialized($extensions, $author);
            $extensions[$author]['conditionOperators'][] = [
                'name' => $conditionOperator::label()->toString(),
                'class' => $conditionOperator::class,
                'package' => $conditionOperator::package()->toString(),
            ];
        }

        foreach ($mutationTypes as $mutationType) {
            $author = $mutationType::author()->toString();
            self::ensureAuthorInitialized($extensions, $author);
            $extensions[$author]['mutationTypes'][] = [
                'name' => $mutationType::label()->toString(),
                'class' => $mutationType::class,
                'package' => $mutationType::package()->toString(),
            ];
        }

        return $extensions;
    }

    /**
     * @param array<string, mixed> $extensions
     */
    private static function ensureAuthorInitialized(array &$extensions, string $author): void
    {
        if (!isset($extensions[$author])) {
            $extensions[$author] = [
                'propertyTypes' => [],
                'widgets' => [],
                'conditionOperators' => [],
                'mutationTypes' => [],
            ];
        }
    }
}
