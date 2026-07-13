<?php

namespace App\Extension\Default\Widget;

interface Projection
{
    public function fetch(): \App\Entity\Projection;
}
