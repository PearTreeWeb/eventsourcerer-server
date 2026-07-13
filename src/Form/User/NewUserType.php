<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\UserId;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NewUserType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('role', RoleType::class)
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => User::class,
            'firstRegistration' => false,
        ]);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $emailAddress = EmailAddress::fromString($forms['email']->getData());

        $user = User::create(
            UserId::fromUuid($this->generateUuid->for($emailAddress)),
            $emailAddress,
            $forms['role']->getData()
        );

        $user->setPassword(self::randomPassword());

        $viewData = $user;
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
    }

    private static function randomPassword(): string
    {
        return md5(\random_bytes(30));
    }
}
