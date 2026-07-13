<?php

namespace App\Domain\Projection\Model;

enum MutationDisplayPart: string
{
    case EventProperty      = 'eventProperty';
    case Label              = 'label';
    case Preposition        = 'preposition';
    case ProjectionProperty = 'projectionProperty';
}
