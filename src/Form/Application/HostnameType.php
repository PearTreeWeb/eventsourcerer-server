<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\Form\DataTransformer\Application\HostnameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HostnameType extends AbstractType
{
    public function __construct(private readonly HostnameTransformer $transformer) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('required', false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
