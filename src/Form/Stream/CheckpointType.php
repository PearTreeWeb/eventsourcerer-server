<?php

declare(strict_types=1);

namespace App\Form\Stream;

use App\Form\DataTransformer\Stream\CheckpointTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class CheckpointType extends AbstractType
{
    public function __construct(private readonly CheckpointTransformer $checkpointTransformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->checkpointTransformer);
    }

    public function getParent(): string
    {
        return IntegerType::class;
    }
}
