<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Projection\Model\ProjectionEventProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Unique;

final class ProjectionEventPropertiesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $uniqueConstraint = new Unique(
            message: 'Properties must have unique names',
            normalizer: self::normalizePropertyType()
        );

        $resolver->setDefaults([
           'allow_add'   => true,
           'constraints' => ['constraints' => $uniqueConstraint],
           'entry_type'  => ProjectionEventPropertyType::class,
           'label'       => false,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    private static function normalizePropertyType(): callable
    {
        return static fn (ProjectionEventProperty $property): string => $property->name->toString();
    }
}
