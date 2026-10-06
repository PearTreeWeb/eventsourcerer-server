<?php

declare(strict_types=1);

namespace App\Extension;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

final class DynamicExtensionLoader implements CompilerPassInterface
{
    private static bool $autoloaderRegistered = false;

    public function __construct(
        private readonly string $extensionDir,
        private readonly string $namespacePrefix = 'App\\Extension\\Dynamic\\'
    ) {}

    public function process(ContainerBuilder $container): void
    {
        if (!is_dir($this->extensionDir)) {
            return;
        }

        $this->registerAutoloader();

        $finder = new Finder();
        $finder->files()->in($this->extensionDir)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $className = $this->namespacePrefix . str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            $definition = $container->register($className, $className)
                ->setAutoconfigured(true)
                ->setAutowired(true);

            // Manually handle AutoconfigureTag attributes because they are normally
            // processed during the discovery phase, which we are supplementing here.
            $attributes = $reflection->getAttributes(AutoconfigureTag::class);
            foreach ($attributes as $attribute) {
                /** @var AutoconfigureTag $instance */
                $instance = $attribute->newInstance();
                $definition->addTag($instance->name, $instance->attributes);
            }
        }
    }

    private function registerAutoloader(): void
    {
        if (self::$autoloaderRegistered) {
            return;
        }

        spl_autoload_register(function ($class) {
            if (str_starts_with($class, $this->namespacePrefix)) {
                $relativePath = str_replace([$this->namespacePrefix, '\\'], ['', DIRECTORY_SEPARATOR], $class) . '.php';
                $file = $this->extensionDir . DIRECTORY_SEPARATOR . $relativePath;
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });

        self::$autoloaderRegistered = true;
    }
}
