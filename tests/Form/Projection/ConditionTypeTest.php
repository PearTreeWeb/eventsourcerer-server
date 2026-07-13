<?php

declare(strict_types=1);

namespace App\Tests\Form\Projection;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Extension\Default\ConditionOperators\NumericEqualTo;
use App\Form\Projection\ConditionType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

interface FormWithIterator extends FormInterface, \IteratorAggregate
{
}

final class ConditionTypeTest extends TestCase
{
    public function testMapFormsToDataValidatesParameterValues(): void
    {
        $conditionType = new ConditionType();

        $formCondition = $this->createMock(FormInterface::class);
        $formCondition->method('getData')->willReturn(NumericEqualTo::class);

        // Mock the collection entries
        $formEntry = $this->createMock(FormInterface::class);
        $formEntry->expects($this->once())
            ->method('addError');

        $iterator = new \ArrayIterator([$formEntry]);
        
        $formParameterValues = $this->createMock(FormWithIterator::class);
        $formParameterValues->method('getData')->willReturn(['invalid']);
        $formParameterValues->method('getIterator')->willReturn($iterator);
        $formParameterValues->method('has')->willReturnMap([['0', true]]);
        $formParameterValues->method('get')->willReturnMap([['0', $formEntry]]);
        $formParameterValues->expects($this->atLeastOnce())
            ->method('addError')
            ->with($this->callback(function (FormError $error) {
                return $error->getMessage() === 'Value "invalid" is incompatible with property type "Numeric"';
            }));

        $forms = new \ArrayIterator([
            'condition' => $formCondition,
            'parameterValues' => $formParameterValues,
        ]);

        $viewData = [];

        $conditionType->mapFormsToData($forms, $viewData);
    }

    public function testMapFormsToDataPassesForValidValues(): void
    {
        $conditionType = new ConditionType();

        $formCondition = $this->createMock(FormInterface::class);
        $formCondition->method('getData')->willReturn(NumericEqualTo::class);

        $formParameterValues = $this->createMock(FormInterface::class);
        $formParameterValues->method('getData')->willReturn(['123']);

        $forms = new \ArrayIterator([
            'condition' => $formCondition,
            'parameterValues' => $formParameterValues,
        ]);

        $viewData = [];

        $conditionType->mapFormsToData($forms, $viewData);

        $this->assertEquals(NumericEqualTo::class, $viewData['condition']);
        $this->assertEquals(['123'], $viewData['parameterValues']);
    }
}
