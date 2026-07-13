<?php

declare(strict_types=1);

namespace App\Form\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventPropertiesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'allow_add'  => true,
           'entry_type' => EventPropertyType::class,
           'label'      => false,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
