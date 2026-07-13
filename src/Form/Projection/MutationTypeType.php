<?php

declare(strict_types=1);

namespace App\Form\Projection;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MutationTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'form');
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
