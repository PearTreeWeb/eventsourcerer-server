<?php

declare(strict_types=1);

namespace App\Form\Projection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectionEndDateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            'required' => false,
        ]);
    }

    public function getParent(): string
    {
        return DateType::class;
    }
}
