<?php

namespace App\Domain\Common\Model;

trait AuthoredBySystem
{
    public static function author(): Author
    {
        return Author::eventSourcerer();
    }
}
