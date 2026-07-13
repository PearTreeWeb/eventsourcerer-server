<?php

declare(strict_types=1);

namespace App\Form\Application;

use App\Form\Stream\CheckpointType;
use App\Form\Stream\StreamIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class EditApplicationStreamCheckpointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('applicationId', ApplicationIdType::class)
            ->add('streamId', StreamIdType::class)
            ->add('checkpoint', CheckpointType::class);
    }
}
