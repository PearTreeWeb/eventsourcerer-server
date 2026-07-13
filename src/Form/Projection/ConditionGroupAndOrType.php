<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Projection\Model\ConditionGroupAndOr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConditionGroupAndOrType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => ConditionGroupAndOr::cases(),
            'choice_label' => fn (?ConditionGroupAndOr $value = null) => $value->value ?? '',
            'choice_value' => fn (?ConditionGroupAndOr $value = null) => $value->value ?? '',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
