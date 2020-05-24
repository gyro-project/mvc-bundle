<?php

namespace Gyro\Bundle\MVCBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GyroMVCExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        /** @psalm-var array{convert_exceptions: array<class-string,string|int>, turbolinks: bool} */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if ($config['convert_exceptions']) {
            $container->setParameter('gyro_mvc.convert_exceptions_map', $config['convert_exceptions']);
        } else {
            $container->removeDefinition('gyro_mvc.convert_exception_listener');
        }

        if ($config['turbolinks']) {
            $loader->load('turbolinks.xml');
        }
    }
}
