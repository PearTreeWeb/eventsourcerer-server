<?php

namespace App;

use App\Extension\DynamicExtensionLoader;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $subscriberId = $_ENV['SUBSCRIBER_ID'] ?? $_SERVER['SUBSCRIBER_ID'] ?? 'default';
        $extensionDir = $this->getProjectDir() . '/var/extensions/' . $subscriberId;

        $container->addCompilerPass(new DynamicExtensionLoader($extensionDir));
    }
}
