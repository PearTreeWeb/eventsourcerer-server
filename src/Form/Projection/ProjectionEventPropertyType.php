<?php

declare(strict_types=1);

namespace App\Form\Projection;

use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Form\Event\EventPropertyTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectionEventPropertyType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', ProjectionEventPropertyIdType::class)
            ->add('name', ProjectionEventPropertyNameType::class)
            ->add('type', EventPropertyTypeType::class)
            ->setDataMapper($this);
    }

    /**
     * @param null|array{id: string, name: string, type: string} $viewData
     * @param \Traversable<string, FormInterface> $forms
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (!$viewData) {
            return;
        }

        $forms = \iterator_to_array($forms);

        $forms['id']->setData(ProjectionEventPropertyId::fromString($viewData['id']));
        $forms['name']->setData(EventPropertyName::fromString($viewData['name']));
        $forms['type']->setData($viewData['type']::create());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = \iterator_to_array($forms);

        /** @var ProjectionEventPropertyId $id */
        $id = $forms['id']->getData();

        $viewData = new ProjectionEventProperty(
            $id,
            $forms['name']->getData(),
            $forms['type']->getData()
        );
    }

    public function getBlockPrefix(): string
    {
        return 'add_event_properties';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'form');
    }
}
