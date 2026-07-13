<?php

namespace App\Domain\Projection\Model;

enum ProjectionStateType: string
{
    case Main = 'main';
    case Reset = 'reset';
}