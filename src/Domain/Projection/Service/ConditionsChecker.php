<?php

declare(strict_types=1);

namespace App\Domain\Projection\Service;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Projection\Model\ConditionGroupAndOr;
use App\Domain\Projection\Model\MutationConditionGroups;
use App\Domain\Projection\Model\ProjectionMutationCondition;
use App\Domain\Projection\Model\ProjectionMutationConditionGroup;
use App\Domain\Projection\Model\ProjectionMutationConditionGroups;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Extension\Default\ConditionOperators\ConditionOperator;

final readonly class ConditionsChecker
{
    /**
     * @param MutationConditionGroups|ProjectionMutationConditionGroups|array<mixed> $conditionGroups
     * @param array<string, mixed>|null $currentState
     */
    public static function isSatisfiedWith(
        MutationConditionGroups|ProjectionMutationConditionGroups|array $conditionGroups,
        PropertyType $propertyType,
        EventPayloadProperty $eventPayloadProperty,
        ?StreamEventPayloadProperties $payloadProperties = null,
        ?array $currentState = null,
    ): bool {
        $satisfied = true;

        if ($conditionGroups instanceof MutationConditionGroups || $conditionGroups instanceof ProjectionMutationConditionGroups) {
            $groups = $conditionGroups->items();
        } else {
            $groups = $conditionGroups;
        }

        foreach ($groups as $conditionGroup) {
            if ($conditionGroup instanceof MutationConditionsGroup) {
                $conditions = $conditionGroup->getConditionsGroup();
            } elseif ($conditionGroup instanceof ProjectionMutationConditionGroup) {
                $conditions = $conditionGroup->items();
            } else {
                $conditions = $conditionGroup->items();
            }

            foreach ($conditions as $condition) {
                $conditionType = match (true) {
                    $condition instanceof MutationCondition => $condition->getType(),
                    $condition instanceof ProjectionMutationCondition => $condition->mutationType->toString(),
                    default => throw new \RuntimeException('Unsupported condition type'),
                };

                /** @var ConditionOperator $operator */
                $operator = new $conditionType();

                $parameters = $operator::parameters();

                $parameterValue = match (true) {
                    $condition instanceof MutationCondition => $condition->getParameterValues(),
                    default => $condition->parameterValues->toArray(),
                };

                $parameter = [];
                foreach ($parameterValue as $key => $val) {
                    if ($val && is_string($val) && (str_contains($val, '{') || str_contains($val, '['))) {
                        $decoded = json_decode($val, true);
                        $parameter[$key] = ($decoded !== null) ? $decoded : $val;
                    } else {
                        $parameter[$key] = $val;
                    }
                }

                if (count($parameters) === 1) {
                    $parameter = array_values($parameter)[0] ?? null;
                }

                if ($operator instanceof \App\Extension\Default\ConditionOperators\InsideProjectionCollection && is_string($parameter)) {
                    $parameter = $currentState[$parameter] ?? [];
                }

                $conditionEventPropertyId = $condition instanceof MutationCondition
                    ? $condition->getEventPropertyId()
                    : $condition->eventPropertyId;
                $conditionEventPropertyType = $condition instanceof MutationCondition
                    ? $condition->getEventPropertyType()
                    : $condition->eventPropertyType;

                $targetPropertyType = $propertyType;
                $targetPayloadProperty = $eventPayloadProperty;

                if (null !== $conditionEventPropertyId && null !== $payloadProperties) {
                    $matchedPayloadProperty = $payloadProperties->items()->first(
                        static fn ($p) => $p->id->toString() === $conditionEventPropertyId
                    );

                    if (null === $matchedPayloadProperty) {
                        $satisfied = false;

                        break;
                    }

                    $targetPayloadProperty = $matchedPayloadProperty->eventPayloadProperty;

                    if (null !== $conditionEventPropertyType && class_exists($conditionEventPropertyType)) {
                        /** @var PropertyType $targetPropertyType */
                        $targetPropertyType = new $conditionEventPropertyType();
                    }
                }

                $satisfied = $operator->compute(
                    $targetPayloadProperty->value->toString(),
                    $parameter,
                );

                if (!$satisfied) {
                    break;
                }
            }

            if (false === $satisfied) {
                break;
            }

            $groupType = match (true) {
                $conditionGroup instanceof MutationConditionsGroup => $conditionGroup->getGroupType(),
                $conditionGroup instanceof ProjectionMutationConditionGroup => $conditionGroup->groupType(),
                default => $conditionGroup->groupType(),
            };

            if (ConditionGroupAndOr::Or === $groupType) {
                break;
            }
        }

        return $satisfied;
    }
}
