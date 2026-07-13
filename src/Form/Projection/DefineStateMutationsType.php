<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Projection\Model\ProjectionMutations;
use App\Entity\Event;
use App\Form\Event\EventIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DefineStateMutationsType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'eventMutations',
                CollectionType::class,
                [
                    'entry_type'    => ProjectionEventMutationType::class,
                    'entry_options' => [
                        'propertyType' => $options['propertyType'],
                    ],
                    'label' => false,
                ]
            )
            ->add('selectedEvent', EventIdType::class)
            ->setDataMapper($this);
    }

    /**
     * @param array{selectedEvent: Event, properties: array<array{id: string, name: string, mutationType: string, type: string}>} $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = \iterator_to_array($forms);
        $forms['selectedEvent']->setData($viewData['selectedEvent']->getId());
        $forms['eventMutations']->setData($viewData['properties']);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        $viewData = ProjectionMutations::fromArray($forms['eventMutations']->getData());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'propertyType' => null,
        ]);
    }
}
