<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Projection\Command\RegisterProjection;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\User\Model\UserId;
use App\Form\Common\UserIdType;
use App\Validator\ProjectionNameUnique;
use App\Validator\StartDateBeforeEndDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegisterProjectionType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userId', UserIdType::class)
            ->add('name', ProjectionNameType::class, [
                'constraints' => [
                    new ProjectionNameUnique(),
                ],
            ])
            ->add('properties', ProjectionEventPropertiesType::class)
            ->add('partition', PartitionByType::class)
            ->add('continuous', ContinuousType::class)
            ->add('exposeStateViaApi', ExposeViaApiType::class)
            ->add('startDate', ProjectionStartDateType::class)
            ->add('endDate', ProjectionEndDateType::class)
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new StartDateBeforeEndDate(),
            ],
        ]);
    }

    /**
     * @param array{userId: UserId} $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = \iterator_to_array($forms);

        $forms['userId']->setData($viewData['userId']);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $name = $forms['name']->getData();

        $id = ProjectionId::fromUuid(
            $this->generateUuid->for($name)
        );

        $viewData = new RegisterProjection(
            $id,
            $name,
            ProjectionEventProperties::fromArray($forms['properties']->getData()),
            $forms['partition']->getData(),
            $forms['continuous']->getData(),
            $forms['exposeStateViaApi']->getData(),
            $forms['startDate']->getData(),
            $forms['endDate']->getData(),
        );
    }
}
