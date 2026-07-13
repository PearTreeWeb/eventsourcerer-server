<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Projection\Model\CanMutate;
use App\Domain\Projection\Model\ConditionGroupAndOr;
use App\Domain\Projection\Model\MutationConditionGroups;
use App\Domain\Projection\Model\ProjectionMutation;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Extension\Default\Mutation\Ignore;
use App\Form\Event\EventPropertyType;
use App\Repository\Mutations;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectionEventMutationType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly GenerateUuid $generateUuid,
        private readonly Mutations $mutations,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $listener = function (FormEvent $event) use ($options): void {
            $form = $event->getForm();

            $form->add(
                'mutationType',
                MutationTypeType::class,
                [
                    'choice_attr'  => static fn (CanMutate $mutate): array => self::dataAttributes($mutate),
                    'choices'      => self::filterCompatible($this->mutations->all(), $options['propertyType']),
                    'choice_label' => static fn (CanMutate $mutate): string => $mutate::label()->toString(),
                    'choice_value' => static fn (?CanMutate $mutate): string => $mutate ? $mutate::class : '',
                    'group_by'     => static fn (CanMutate $mutate): string => $mutate->author()->toString(),
                    'placeholder'  => Ignore::label()->toString(),
                ]
            );
        };

        $builder
            ->add('eventProperty', EventPropertyType::class)
            ->add('mutationType', MutationTypeType::class)
            ->add('conditionGroups', ConditionGroupsType::class, ['entry_options' => ['propertyType' => $options['propertyType']]])
            ->addEventListener(FormEvents::PRE_SET_DATA, $listener)
            ->setDataMapper($this);
    }

    /**
     * @param array{id: string, name: string, type: string, mutationType: CanMutate, conditionGroups: Collection<string, MutationConditionsGroup>} $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = \iterator_to_array($forms);

        $forms['eventProperty']->setData($viewData);
        $forms['mutationType']->setData($viewData['mutationType']);
        $forms['conditionGroups']->setData($viewData['conditionGroups']);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        /** @var EventProperty $eventProperty */
        $eventProperty = $forms['eventProperty']->getData();

        /** @var array<array{group: array<array{condition: class-string, parameterValues: array<string, mixed>}>, conditionGroupType: ConditionGroupAndOr}> $persistentCollectionOfConditionGroups */
        $persistentCollectionOfConditionGroups = $forms['conditionGroups']->getData();

        $viewData = new ProjectionMutation(
            ProjectionMutationId::fromUuid($this->generateUuid->random()),
            $eventProperty->id,
            $forms['mutationType']->getData(),
            $this->conditionGroups($persistentCollectionOfConditionGroups),
        );
    }

    /**
     * @return array{data-preposition: string}
     */
    private static function dataAttributes(CanMutate $mutation): array
    {
        return [
            'data-preposition' => $mutation->preposition()->value,
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'propertyType' => null,
            'label' => false,
        ]);
    }

    /**
     * @param iterable<CanMutate> $mutations
     *
     * @return iterable<CanMutate>
     */
    private static function filterCompatible(iterable $mutations, PropertyType $propertyType): iterable
    {
        foreach ($mutations as $mutation) {
            if ($mutation->compatibleWith($propertyType)) {
                yield $mutation;
            }
        }
    }

    /**
     * @param array<array{
     *     conditionGroupType: ConditionGroupAndOr,
     *     group: array<array{
     *         condition: class-string,
     *         parameterValues: array<string, mixed>,
     *     }>
     * }> $rawConditionGroups
     */
    private function conditionGroups(array $rawConditionGroups): MutationConditionGroups
    {
        $groups = new MutationConditionGroups();

        foreach ($rawConditionGroups as $rawConditionGroup) {
            $group = MutationConditionsGroup::create($rawConditionGroup['conditionGroupType']);

            $conditions = array_map(
                static fn (array $condition) => MutationCondition::create(
                    $condition['condition'],
                    $group,
                    $condition['parameterValues'],
                ),
                $rawConditionGroup['group'],
            );

            $group->setConditions($conditions);
            $groups->add($group);
        }

       return $groups;
    }
}
