<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Form\DataTransformer\Event\EventIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class EventIdType extends AbstractType
{
    public function __construct(private readonly EventIdTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
