<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MutationCondition
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'id')]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $type;

    #[ORM\ManyToOne(targetEntity: MutationConditionsGroup::class, inversedBy: 'conditionsGroup')]
    private MutationConditionsGroup $conditionsGroup;

    /** @var array<string, mixed> */
    #[ORM\Column(type: Types::JSON)]
    private array $parameterValues = [];

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $eventPropertyId = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $eventPropertyType = null;

    /**
     * @param array<string, mixed> $parameterValue
     */
    public static function create(
        string $type,
        MutationConditionsGroup $conditionsGroup,
        array $parameterValue = [],
        ?string $eventPropertyId = null,
        ?string $eventPropertyType = null,
    ): self {
        $entity = new self();

        $entity->type = $type;
        $entity->conditionsGroup = $conditionsGroup;
        $entity->parameterValues = $parameterValue;
        $entity->eventPropertyId = $eventPropertyId;
        $entity->eventPropertyType = $eventPropertyType;

        return $entity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameterValues(): array
    {
        return $this->parameterValues;
    }

    public function getConditionsGroup(): MutationConditionsGroup
    {
        return $this->conditionsGroup;
    }

    public function getEventPropertyId(): ?string
    {
        return $this->eventPropertyId;
    }

    public function getEventPropertyType(): ?string
    {
        return $this->eventPropertyType;
    }
}
