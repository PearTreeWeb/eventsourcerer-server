<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\Form\DataTransformer\Application\ApplicationNameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ApplicationNameType extends AbstractType
{
    public function __construct(private readonly ApplicationNameTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
