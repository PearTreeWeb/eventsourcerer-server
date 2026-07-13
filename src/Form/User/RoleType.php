<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Domain\User\Model\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RoleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class'              => Role::class,
            'translation_domain' => 'form',
        ]);
    }

    public function getParent(): string
    {
        return EnumType::class;
    }
}
