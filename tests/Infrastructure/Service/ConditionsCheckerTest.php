<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\ConditionGroupAndOr;
use App\Domain\Projection\Model\ConditionParameterValues;
use App\Domain\Projection\Model\MutationType;
use App\Domain\Projection\Model\ProjectionMutationCondition;
use App\Domain\Projection\Model\ProjectionMutationConditionGroup;
use App\Domain\Projection\Model\ProjectionMutationConditionGroupKey;
use App\Domain\Projection\Model\ProjectionMutationConditionGroups;
use App\Domain\Projection\Service\ConditionsChecker;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Extension\Default\ConditionOperators\GreaterThan;
use App\Extension\Default\ConditionOperators\LessThan;
use App\Extension\Default\PropertyType\Integer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConditionsCheckerTest extends TestCase
{
    #[Test]
    public function itIsSatisfiedWithProjectionMutationConditionGroups(): void
    {
        $condition1 = new ProjectionMutationCondition(
            ProjectionMutationConditionGroupKey::fromInt(1),
            MutationType::fromString(GreaterThan::class),
            ConditionParameterValues::fromArray(['400'])
        );

        $group1 = ProjectionMutationConditionGroup::fromArray([$condition1])
            ->withGroupType(ConditionGroupAndOr::And);

        $groups = ProjectionMutationConditionGroups::fromArray([$group1]);

        $this->assertTrue(
            ConditionsChecker::isSatisfiedWith(
                $groups,
                new Integer(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('price'),
                    EventPropertyValue::fromString('2500')
                )
            )
        );

        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                $groups,
                new Integer(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('price'),
                    EventPropertyValue::fromString('300')
                )
            )
        );
    }

    #[Test]
    public function itIsSatisfiedWithOneMutationCondition(): void
    {
        $mutationConditionsGroup1 = MutationConditionsGroup::create(
            ConditionGroupAndOr::And,
            []
        );

        $mutationCondition1 = MutationCondition::create(
            GreaterThan::class,
            $mutationConditionsGroup1,
            ['400'],
        );

        $mutationConditionsGroup1->setConditions([$mutationCondition1]);

        $this->assertTrue(
            ConditionsChecker::isSatisfiedWith(
                [
                    $mutationConditionsGroup1,
                ],
                new Integer(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('price'),
                    EventPropertyValue::fromString('2500')
                )
            )
        );
    }

    #[Test]
    public function itIsNotSatisfiedWhenOneGroupIsDissatisfied(): void
    {
        $mutationConditionsGroup1 = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition1 = MutationCondition::create(
            GreaterThan::class,
            $mutationConditionsGroup1,
            ['400'],
        );

        $mutationConditionsGroup1->setConditions([$mutationCondition1]);

        $mutationConditionsGroup2 = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition2 = MutationCondition::create(
            LessThan::class,
            $mutationConditionsGroup2,
            ['1000'],
        );

        $mutationConditionsGroup2->setConditions([$mutationCondition2]);

        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                [
                    $mutationConditionsGroup1,
                    $mutationConditionsGroup2,
                ],
                new Integer(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('price'),
                    EventPropertyValue::fromString('2500')
                )
            )
        );
    }

    #[Test]
    public function itIsSatisfiedWhenAtLeastOneGroupIsSatisfied(): void
    {
        $mutationConditionsGroup1 = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition1 = MutationCondition::create(
            GreaterThan::class,
            $mutationConditionsGroup1,
            ['10000'],
        );

        $mutationConditionsGroup1->setConditions([$mutationCondition1]);

        $mutationConditionsGroup2 = MutationConditionsGroup::create(ConditionGroupAndOr::Or);

        $mutationCondition2 = MutationCondition::create(
            LessThan::class,
            $mutationConditionsGroup2,
            ['3000'],
        );

        $mutationConditionsGroup2->setConditions([$mutationCondition2]);

        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                [
                    $mutationConditionsGroup1,
                    $mutationConditionsGroup2,
                ],
                new Integer(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('price'),
                    EventPropertyValue::fromString('2500')
                )
            )
        );
    }
}
