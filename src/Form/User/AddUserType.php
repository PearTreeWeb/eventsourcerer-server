<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class AddUserType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', NewUserType::class)
            ->setDataMapper($this);
    }

    /**
     * @param null|User $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $forms = \iterator_to_array($forms);

        $forms['user']->setData($viewData);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        /** @var User $user */
        $user = $forms['user']->getData();

        $viewData = $user;
    }
}
