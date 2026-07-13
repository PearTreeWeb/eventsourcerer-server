<?php

namespace App\Form\Tool;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

final class EncryptPersonalDataFormType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('beforeDate', DateType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        // TODO: Implement mapDataToForms() method.
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $data = iterator_to_array($forms);

        $viewData = [
            'beforeDate' => \DateTimeImmutable::createFromMutable($data['beforeDate']->getData()),
        ];
    }
}
