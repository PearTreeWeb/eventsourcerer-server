<?php

declare(strict_types=1);

namespace App\Form\Projection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConditionGroupsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_options' => ['propertyType' => null],
            'entry_type' => ConditionGroupType::class,
            'allow_add' => true,
            'label' => false,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
