<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Form\DataTransformer\Projection\ProjectionIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProjectionIdType extends AbstractType
{
    public function __construct(private readonly ProjectionIdTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
