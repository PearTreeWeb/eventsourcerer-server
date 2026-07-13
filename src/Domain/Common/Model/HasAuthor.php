<?php

namespace App\Domain\Common\Model;

interface HasAuthor
{
    public static function author(): Author;
}
