<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\PropertyType;
use App\Entity\MutationCondition;
use App\Extension\Default\ConditionOperators\ConditionOperator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConditionType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $conditionTypes = $options['propertyType']::conditionOperators();

        /** @var array<array{id: string, name: string, type: class-string<PropertyType>}> $eventProperties */
        $eventProperties = $options['eventProperties'] ?? [];

        foreach ($eventProperties as $eventProperty) {
            $conditionTypes = array_merge($conditionTypes, $eventProperty['type']::conditionOperators());
        }

        /** @var array<class-string<ConditionOperator>> $mergedConditionTypes */
        $mergedConditionTypes = $conditionTypes;
        $conditionTypes = \array_values(\array_unique($mergedConditionTypes));

        $eventPropertyTypesById = array_column($eventProperties, 'type', 'id');

        $builder
            ->add(
                'eventProperty',
                ChoiceType::class,
                [
                    'label' => 'Event property',
                    'required' => false,
                    'placeholder' => 'this property',
                    'choices' => array_column($eventProperties, 'id', 'name'),
                    'choice_attr' => static fn (string $eventPropertyId) => [
                        'data-type' => $eventPropertyTypesById[$eventPropertyId] ?? '',
                    ],
                ]
            )
            ->add(
                'condition',
                ChoiceType::class,
                [
                    'label' => 'Event property value',
                    'choices' => $conditionTypes,
                    'choice_label' => static fn (string $conditionClassString) => strtolower($conditionClassString::label()->toString()),
                    'group_by' => static fn (string $conditionClassString) => $conditionClassString::author()->toString(),
                    'choice_attr' => static fn (string $conditionClassString) => [
                        'data-parameters' => json_encode($conditionClassString::parameters()),
                    ],
                ]
            )
            ->add(
                'parameterValues',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'label' => false,
                    'by_reference' => false,
                    'error_bubbling' => false,
                ]
            )
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'propertyType' => '',
            'eventProperties' => [],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'condition_type';
    }

    /**
     * @param null|MutationCondition $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['condition']->setData($viewData->getType());

        if (isset($forms['eventProperty'])) {
            $forms['eventProperty']->setData($viewData->getEventPropertyId());
        }

        $parameterValues = $viewData->getParameterValues();
        $parameterValues = array_map(function ($value) {
            if (is_array($value)) {
                return json_encode($value);
            }
            return (string) $value;
        }, $parameterValues);

        $forms['parameterValues']->setData($parameterValues);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        /** @var class-string<ConditionOperator> $condition */
        $condition = $forms['condition']->getData();
        $parameterValues = $forms['parameterValues']->getData();

        /** @var ?string $eventPropertyId */
        $eventPropertyId = isset($forms['eventProperty']) ? ($forms['eventProperty']->getData() ?: null) : null;

        $eventPropertyType = null;

        if (null !== $eventPropertyId) {
            /** @var array<array{id: string, name: string, type: class-string<PropertyType>}> $eventProperties */
            $eventProperties = $forms['eventProperty']->getParent()?->getConfig()->getOption('eventProperties') ?? [];
            $eventPropertyType = array_column($eventProperties, 'type', 'id')[$eventPropertyId] ?? null;
        }

        if (is_array($parameterValues)) {
            $parameterValues = array_values($parameterValues);
            $parameterValues = array_map(static function ($value) {
                if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                    return json_decode($value, true, 512, JSON_THROW_ON_ERROR) ?? $value;
                }
                return $value;
            }, $parameterValues);

            if ($condition) {
                $parameterPropertyTypes = $condition::parameterPropertyTypes();
                foreach ($parameterValues as $index => $value) {
                    if (isset($parameterPropertyTypes[$index])) {
                        try {
                            /** @var class-string<PropertyType> $propertyType */
                            $propertyType = $parameterPropertyTypes[$index];
                            $propertyType::validate($value);
                        } catch (PropertyTypeValueIsIncompatible $e) {
                            $error = new FormError($e->getMessage());
                            /** @var FormInterface $parameterValuesForm */
                            $parameterValuesForm = $forms['parameterValues'];
                            $parameterValuesForm->addError($error);

                            if ($parameterValuesForm->has((string) $index)) {
                                $parameterValuesForm->get((string) $index)->addError($error);
                            }
                        }
                    }
                }
            }
        }

        $viewData = [
            'condition' => $condition,
            'parameterValues' => $parameterValues,
            'eventPropertyId' => $eventPropertyId,
            'eventPropertyType' => $eventPropertyType,
        ];
    }
}
