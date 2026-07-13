<?php

declare(strict_types=1);

namespace App\Tests\Domain\Projection\Model;

use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Projection\Model\Condition;
use App\Domain\Projection\Model\ConditionParameterValues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConditionTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $uniqueIdentifier = AuthorUniqueIdentifier::fromString('test-author');
        $parameterValues = ConditionParameterValues::fromArray(['test-value']);

        $condition = new Condition($uniqueIdentifier, $parameterValues);

        $this->assertSame($uniqueIdentifier, $condition->uniqueIdentifier);
        $this->assertSame($parameterValues, $condition->parameterValues);
    }

    #[Test]
    public function itGeneratesCorrectKey(): void
    {
        $uniqueIdentifier = AuthorUniqueIdentifier::fromString('test-author');
        $parameterValues = ConditionParameterValues::fromArray(['test-value']);

        $condition = new Condition($uniqueIdentifier, $parameterValues);

        $expectedKey = sprintf('%s-%s', $uniqueIdentifier, $parameterValues->items()->implode('-'));
        $this->assertEquals($expectedKey, $condition->key());
    }

    #[Test]
    public function itGeneratesDifferentKeysForDifferentConditions(): void
    {
        $uniqueIdentifier1 = AuthorUniqueIdentifier::fromString('author-1');
        $uniqueIdentifier2 = AuthorUniqueIdentifier::fromString('author-2');
        $parameterValues1 = ConditionParameterValues::fromArray(['value-1']);
        $parameterValues2 = ConditionParameterValues::fromArray(['value-2']);

        $condition1 = new Condition($uniqueIdentifier1, $parameterValues1);
        $condition2 = new Condition($uniqueIdentifier1, $parameterValues2);
        $condition3 = new Condition($uniqueIdentifier2, $parameterValues1);
        $condition4 = new Condition($uniqueIdentifier2, $parameterValues2);

        $keys = [
            $condition1->key(),
            $condition2->key(),
            $condition3->key(),
            $condition4->key(),
        ];

        $this->assertCount(4, array_unique($keys), 'All conditions should have unique keys');
    }

    #[Test]
    public function itGeneratesSameKeyForIdenticalConditions(): void
    {
        $uniqueIdentifier = AuthorUniqueIdentifier::fromString('test-author');
        $parameterValues = ConditionParameterValues::fromArray(['test-value']);

        $condition1 = new Condition($uniqueIdentifier, $parameterValues);
        $condition2 = new Condition($uniqueIdentifier, $parameterValues);

        $this->assertEquals($condition1->key(), $condition2->key());
    }
}
