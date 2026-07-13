<?php

declare(strict_types=1);

namespace App\Form\Event;

use App\Domain\Common\Model\PropertyType;
use App\Repository\PropertyTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EventPropertyTypeType extends AbstractType
{
    public function __construct(private readonly PropertyTypes $propertyTypes) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = iterator_to_array($this->propertyTypes->all());

        $resolver->setDefaults([
            'block_prefix' => 'add_event_properties_type',
            'choices'      => $choices,
            'choice_value' => ChoiceList::value($this, self::choiceValue()),
            'choice_label' => static fn (PropertyType $propertyType)  => $propertyType::name()->toString(),
            'group_by'     => static fn (PropertyType $propertyType)  => $propertyType::author()->toString(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    private static function choiceValue(): callable
    {
        return static function (PropertyType|string|null $propertyType) {
            if (null === $propertyType) {
                return null;
            }

            // is a string when editing an existing property
            if (is_string($propertyType)) {
                return $propertyType;
            }

            return $propertyType::class;
        };
    }
}
