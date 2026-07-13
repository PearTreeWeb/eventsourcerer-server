<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Entity\MutationConditionsGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConditionGroupType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conditionGroupType', ConditionGroupAndOrType::class)
            ->add('group', ConditionsType::class, ['entry_options' => ['propertyType' => $options['propertyType']]])
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('propertyType', null);
    }

    /**
     * @param ?MutationConditionsGroup $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = iterator_to_array($forms);

        if (null === $viewData) {
            return;
        }

        $forms['conditionGroupType']->setData($viewData->getGroupType());
        $forms['group']->setData($viewData->getConditionsGroup());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $viewData = [
            'group' => $forms['group']->getData(),
            'conditionGroupType' => $forms['conditionGroupType']->getData(),
        ];
    }

    public function getBlockPrefix(): string
    {
        return 'condition_group_type';
    }
}
