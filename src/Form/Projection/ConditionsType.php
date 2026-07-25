<?php

declare(strict_types=1);

namespace App\Form\Projection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConditionsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => ConditionType::class,
            'allow_add' => true,
            'propertyType' => null,
            'eventProperties' => [],
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'condition_group_conditions_type';
    }
}
