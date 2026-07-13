<?php

declare(strict_types=1);

namespace App\Form\Widget;

use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectionChoiceType extends AbstractType
{
    public function __construct(private readonly ProjectionRepository $projectionRepository) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices'      => $this->projectionRepository->all(),
            'choice_label' => static fn (Projection $projection) => $projection->getName(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
