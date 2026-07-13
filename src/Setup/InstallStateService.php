<?php

declare(strict_types=1);

namespace App\Setup;

final class InstallStateService
{
    private readonly string $markerFile;

    public function __construct(string $projectDir)
    {
        $this->markerFile = $projectDir . '/var/.installed';
    }

    public function isInstalled(): bool
    {
        return file_exists($this->markerFile);
    }

    public function markInstalled(): void
    {
        if ($this->isInstalled()) {
            return;
        }

        @file_put_contents($this->markerFile, (new \DateTimeImmutable())->format(DATE_ATOM) . "\n");
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->markerFile, true);
        }
    }

    public function reset(): void
    {
        if ($this->isInstalled()) {
            @unlink($this->markerFile);
        }
    }

    public function markerFile(): string
    {
        return $this->markerFile;
    }
}
