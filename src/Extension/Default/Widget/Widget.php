<?php

namespace App\Extension\Default\Widget;

use App\Domain\Common\Model\HasAuthor;
use App\Domain\Common\Model\Package;
use App\Domain\Widget\Model\WidgetName;

interface Widget extends HasAuthor
{
    public static function name(): WidgetName;

    public function view(): string;

    public static function package(): Package;
}
