<?php

declare(strict_types=1);

namespace App\Form\Stream;

use App\Form\DataTransformer\Application\ApplicationIdTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

final class StreamIdType extends HiddenType
{
    public function __construct(private readonly ApplicationIdTransformer $streamIdTransformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->streamIdTransformer);
    }
}
