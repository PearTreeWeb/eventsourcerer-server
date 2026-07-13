<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Form\DataTransformer\Event\EventPropertyNameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class EventPropertyNameType extends AbstractType
{
    public function __construct(private readonly EventPropertyNameTransformer $transformer)
    {
    }

    public function getBlockPrefix(): string
    {
        return 'add_event_properties_name';
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
