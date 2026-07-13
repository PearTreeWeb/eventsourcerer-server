<?php

declare(strict_types=1);

namespace App\Form\Common;

use App\Form\DataTransformer\User\UserIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class UserIdType extends AbstractType
{
    public function __construct(private readonly UserIdTransformer $transformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
