<?php

declare(strict_types=1);

namespace App\Form\Widget;

use App\Domain\Widget\Command\RegisterWidget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class RegisterWidgetType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', WidgetNameType::class)
            ->add('type', WidgetTypeType::class)
            ->add('projection', ProjectionChoiceType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        // TODO: Implement mapDataToForms() method.
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $viewData = new RegisterWidget(
            $forms['name']->getData(),
            $forms['type']->getData(),
            $forms['projection']->getData()
        );
    }
}
