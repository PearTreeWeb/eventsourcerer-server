<?php

namespace App\Domain\Projection\Model;

enum ProjectionCondition: string
{
    case Failed = 'failed';
    case Finished = 'finished';
    case Resetting = 'resetting';
    case Running = 'running';
}