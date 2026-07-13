<?php

namespace App\Domain\Projection\Model;

enum ConditionGroupAndOr: string
{
    case And = 'and';
    case Or = 'or';
}