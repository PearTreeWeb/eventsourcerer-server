<?php

declare(strict_types=1);

namespace App\Form\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TombstoneAfterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'empty_data' => 0,
            'required' => false,
        ]);
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }
}
