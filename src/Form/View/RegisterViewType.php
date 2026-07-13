<?php

declare(strict_types=1);

namespace App\Form\View;

use App\Form\Event\EventPropertiesType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class RegisterViewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('properties', EventPropertiesType::class);
    }
}
