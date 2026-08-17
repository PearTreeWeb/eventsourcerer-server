<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Command\RegisterEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;
use App\Entity\Author;
use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

final class RegisterEventType extends AbstractType implements DataMapperInterface
{
    public function __construct(private readonly GenerateUuid $generateUuid) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', EventNameType::class)
            ->add('tombstoneAfter', TombstoneAfterType::class)
            ->add('properties', EventPropertiesType::class)
            ->add('authorId', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'required' => false,
                'placeholder' => '',
            ])
            ->setDataMapper($this);
    }

    /**
     * @param ?Event $viewData
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $forms = iterator_to_array($forms);

        if (!$viewData) {
            $forms['tombstoneAfter']->setData(0);

            return;
        }

        $forms['name']->setData(EventName::fromString($viewData->getName()));
        $forms['properties']->setData($viewData->getProperties()->toArray());
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $eventName = $forms['name']->getData();

        /** @var ?Author $author */
        $author = $forms['authorId']->getData();

        $viewData = new RegisterEvent(
            EventId::fromUuid($this->generateUuid->for($eventName)),
            $eventName,
            EventProperties::fromArray($forms['properties']->getData()),
            $forms['tombstoneAfter']->getData() ?? 0,
            $author?->getId()
        );
    }
}
