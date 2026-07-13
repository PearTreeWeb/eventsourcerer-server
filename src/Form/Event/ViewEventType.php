<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Domain\Event\Model\EventName;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class ViewEventType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', EventIdType::class, ['disabled' => true])
            ->add('name', EventNameType::class, ['disabled' => true])
            ->add('properties', EventPropertiesType::class, ['disabled' => true])
            ->setDataMapper($this);
    }

    /**
     * @param ?Event $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (!$viewData) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['id']->setData($viewData->getId());
        $forms['name']->setData(EventName::fromString($viewData->getName()));
        $forms['properties']->setData($viewData->eventProperties()->asArray());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
    }
}
