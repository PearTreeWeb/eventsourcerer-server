<?php

declare(strict_types=1);

namespace App\Form\Widget;

use App\Domain\Widget\Model\WidgetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WidgetTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => fn (WidgetType $widgetType) => $widgetType->value,
            'choices'      => WidgetType::cases(),
            'placeholder'  => '-- select --',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
