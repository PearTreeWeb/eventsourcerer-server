<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Projection\Command\UpdateProjection;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\User\Model\UserId;
use App\Form\Common\UserIdType;
use App\Validator\StartDateBeforeEndDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EditProjectionType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('projectionId', ProjectionIdType::class)
            ->add('userId', UserIdType::class)
            ->add('projectionName', ProjectionNameType::class)
            ->add('continuous', ContinuousType::class)
            ->add('exposeStateViaApi', ExposeViaApiType::class)
            ->add('partition', PartitionByType::class)
            ->add('properties', ProjectionEventPropertiesType::class)
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
     * @param array{
     *     projectionId: ProjectionId,
     *     userId: UserId,
     *     projectionName: string,
     *     properties: array<array{
     *          id: string,
     *          name: string,
     *          type: string,
     *     }>,
     *     partition: bool,
     *     continuous: bool,
     *     exposeStateViaApi: bool,
     *     startDate: ?\DateTimeImmutable,
     *     endDate: ?\DateTimeImmutable,
     * } $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = \iterator_to_array($forms);

        $forms['projectionId']->setData($viewData['projectionId']);
        $forms['userId']->setData($viewData['userId']);
        $forms['properties']->setData($viewData['properties']);
        $forms['continuous']->setData($viewData['continuous']);
        $forms['projectionName']->setData(ProjectionName::fromString($viewData['projectionName']));
        $forms['partition']->setData($viewData['partition']);
        $forms['exposeStateViaApi']->setData($viewData['exposeStateViaApi']);
        $forms['startDate']->setData($viewData['startDate']);
        $forms['endDate']->setData($viewData['endDate']);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $name = $forms['projectionName']->getData();

        $viewData = new UpdateProjection(
            $forms['projectionId']->getData(),
            $name,
            ProjectionEventProperties::fromArray($forms['properties']->getData()),
            $forms['partition']->getData(),
            $forms['exposeStateViaApi']->getData(),
            $forms['startDate']->getData(),
            $forms['endDate']->getData(),
        );
    }
}
