<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Form\DataTransformer\Event\EventNameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class EventNameType extends AbstractType
{
    public function __construct(private readonly EventNameTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
