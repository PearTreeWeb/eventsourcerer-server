<?php

namespace App\Domain\Projection\Model;

use PearTreeWeb\EventSourcerer\Common\Model\FulfilIsInteger;
use PearTreeWeb\EventSourcerer\Common\Model\IsInteger;

final class ProjectionMutationConditionGroupKey implements IsInteger
{
    use FulfilIsInteger;
}
