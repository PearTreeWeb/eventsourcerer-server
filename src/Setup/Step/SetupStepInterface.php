<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Symfony\Component\HttpFoundation\Request;

interface SetupStepInterface
{
    public function label(): string;

    public function run(Request $request): SetupStepResult;
}
