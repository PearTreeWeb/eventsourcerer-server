<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
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
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Domain\Stream\Model\StreamEventPayloadProperty;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\GreaterThan;
use App\Extension\Default\ConditionOperators\LessThan;
use App\Extension\Default\PropertyType\Integer;
use App\Extension\Default\PropertyType\Text;
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

    #[Test]
    public function itEvaluatesAConditionAgainstADifferentEventProperty(): void
    {
        $surnamePropertyId = '11111111-1111-1111-1111-111111111111';

        $mutationConditionsGroup = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition = MutationCondition::create(
            EqualTo::class,
            $mutationConditionsGroup,
            ['Smith'],
            $surnamePropertyId,
            Text::class,
        );

        $mutationConditionsGroup->setConditions([$mutationCondition]);

        $candidateIdProperty = new EventPayloadProperty(
            EventPropertyName::fromString('candidate-id'),
            EventPropertyValue::fromString('2500')
        );

        $payloadProperties = StreamEventPayloadProperties::fromArray([
            new StreamEventPayloadProperty(
                EventPropertyId::fromString('22222222-2222-2222-2222-222222222222'),
                $candidateIdProperty,
            ),
            new StreamEventPayloadProperty(
                EventPropertyId::fromString($surnamePropertyId),
                new EventPayloadProperty(
                    EventPropertyName::fromString('surname'),
                    EventPropertyValue::fromString('Smith')
                ),
            ),
        ]);

        $this->assertTrue(
            ConditionsChecker::isSatisfiedWith(
                [$mutationConditionsGroup],
                new Integer(),
                $candidateIdProperty,
                $payloadProperties,
            )
        );
    }

    #[Test]
    public function itIsNotSatisfiedWhenTheOtherEventPropertyDoesNotMatch(): void
    {
        $surnamePropertyId = '11111111-1111-1111-1111-111111111111';

        $mutationConditionsGroup = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition = MutationCondition::create(
            EqualTo::class,
            $mutationConditionsGroup,
            ['Smith'],
            $surnamePropertyId,
            Text::class,
        );

        $mutationConditionsGroup->setConditions([$mutationCondition]);

        $candidateIdProperty = new EventPayloadProperty(
            EventPropertyName::fromString('candidate-id'),
            EventPropertyValue::fromString('2500')
        );

        $payloadProperties = StreamEventPayloadProperties::fromArray([
            new StreamEventPayloadProperty(
                EventPropertyId::fromString($surnamePropertyId),
                new EventPayloadProperty(
                    EventPropertyName::fromString('surname'),
                    EventPropertyValue::fromString('Jones')
                ),
            ),
        ]);

        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                [$mutationConditionsGroup],
                new Integer(),
                $candidateIdProperty,
                $payloadProperties,
            )
        );
    }

    #[Test]
    public function itIsNotSatisfiedWhenTheOtherEventPropertyIsMissingFromThePayload(): void
    {
        $mutationConditionsGroup = MutationConditionsGroup::create(ConditionGroupAndOr::And);

        $mutationCondition = MutationCondition::create(
            EqualTo::class,
            $mutationConditionsGroup,
            ['Smith'],
            '11111111-1111-1111-1111-111111111111',
            Text::class,
        );

        $mutationConditionsGroup->setConditions([$mutationCondition]);

        $candidateIdProperty = new EventPayloadProperty(
            EventPropertyName::fromString('candidate-id'),
            EventPropertyValue::fromString('2500')
        );

        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                [$mutationConditionsGroup],
                new Integer(),
                $candidateIdProperty,
                StreamEventPayloadProperties::fromArray([]),
            )
        );
    }
    #[Test]
    public function itIsSatisfiedWithInsideProjectionCollection(): void
    {
        $condition1 = new ProjectionMutationCondition(
            ProjectionMutationConditionGroupKey::fromInt(1),
            MutationType::fromString(\App\Extension\Default\ConditionOperators\InsideProjectionCollection::class),
            ConditionParameterValues::fromArray(['my_collection'])
        );

        $group1 = ProjectionMutationConditionGroup::fromArray([$condition1])
            ->withGroupType(ConditionGroupAndOr::And);

        $groups = ProjectionMutationConditionGroups::fromArray([$group1]);

        $currentState = [
            'my_collection' => ['val1', 'val2']
        ];

        // Satisfied
        $this->assertTrue(
            ConditionsChecker::isSatisfiedWith(
                $groups,
                new Text(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('v'),
                    EventPropertyValue::fromString('val1')
                ),
                null,
                $currentState
            )
        );

        // Not satisfied (value not in collection)
        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                $groups,
                new Text(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('v'),
                    EventPropertyValue::fromString('val3')
                ),
                null,
                $currentState
            )
        );

        // Not satisfied (collection property doesn't exist in state)
        $this->assertFalse(
            ConditionsChecker::isSatisfiedWith(
                $groups,
                new Text(),
                new EventPayloadProperty(
                    EventPropertyName::fromString('v'),
                    EventPropertyValue::fromString('val1')
                ),
                null,
                ['other_property' => []]
            )
        );
    }
}
