<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Command\EditEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class EditEventType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', EventIdType::class)
            ->add('name', EventNameType::class)
            ->add('properties', EventPropertiesType::class)
            ->add('tombstoneAfter', TombstoneAfterType::class)
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
        $forms['tombstoneAfter']->setData($viewData->getTombstoneAfterSeconds());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms     = iterator_to_array($forms);
        $eventName = $forms['name']->getData();

        $viewData = new EditEvent(
            $forms['id']->getData(),
            EventId::fromUuid($this->generateUuid->random()),
            $eventName,
            EventProperties::fromArray($forms['properties']->getData()),
            $forms['tombstoneAfter']->getData()
        );
    }
}
