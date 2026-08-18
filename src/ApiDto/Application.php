<?php

declare(strict_types=1);

namespace App\ApiDto;

final class Application
{
    public string $name;
    public ?string $hostname = null;
    public ?string $id = null;
    public ?string $secret = null;
}
