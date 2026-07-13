<?php

namespace App\Domain\Common\Model;

enum Packages: string
{
    case Base = 'Base';
    case Geo = 'Geo';
    case Network = 'Network';
    case PersonalData = 'PersonalData';
}
