<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventPropertyType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'form');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', EventPropertyIdType::class)
            ->add('name', EventPropertyNameType::class)
            ->add('type', EventPropertyTypeType::class)
            ->add('containsPersonalData', EventPropertyPersonalDataType::class)
            ->setDataMapper($this);
    }

    /**
     * @param null|array{id: string, name: string, type: PropertyType, containsPersonalData: bool} $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (!$viewData) {
            return;
        }

        $forms = \iterator_to_array($forms);

        $forms['id']->setData(EventPropertyId::fromString($viewData['id']));
        $forms['name']->setData(EventPropertyName::fromString($viewData['name']));
        $forms['type']->setData($viewData['type']);
        $forms['containsPersonalData']->setData($viewData['containsPersonalData']);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        /** @var EventPropertyId $id */
        $id = $forms['id']->getData();

        if ($id->isNull()) {
            $id = EventPropertyId::fromUuid($this->generateUuid->random());
        }

        $viewData = new EventProperty(
            $id,
            $forms['name']->getData(),
            $forms['type']->getData(),
            $forms['containsPersonalData']->getData(),
        );
    }

    public function getBlockPrefix(): string
    {
        return 'add_event_properties';
    }
}
