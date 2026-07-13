<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\User as DomainUser;
use App\Domain\User\Model\UserId;
use App\Entity\User;
use App\Form\Common\UserIdType;
use App\Validator\HasAtLeastOneSuperUserConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EditUserType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isSuperUser = $options['is_super_user'];

        $builder
            ->add('id', UserIdType::class)
            ->add('email')
            ->add('role', RoleType::class, [
                'disabled'    => !$isSuperUser,
                'constraints' => [
                    new HasAtLeastOneSuperUserConstraint(),
                ],
            ])
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['is_super_user' => false]);
        $resolver->setAllowedTypes('is_super_user', 'bool');
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

        $forms['id']->setData($viewData->getId());
        $forms['email']->setData(EmailAddress::fromString($viewData->getEmail())->toString());
        $forms['role']->setData(Role::from($viewData->getRoles()[0]));
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $viewData = new DomainUser(
            $forms['id']->getData(),
            EmailAddress::fromString($forms['email']->getData()),
            $forms['role']->getData(),
        );
    }
}
