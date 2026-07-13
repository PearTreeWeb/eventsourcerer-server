<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

enum MutationPreposition: string
{
    case BY             = 'by';
    case FROM           = 'from';
    case IN             = 'in';
    case NOT_APPLICABLE = 'n/a';
    case TO             = 'to';
    case USING          = 'using';
}
