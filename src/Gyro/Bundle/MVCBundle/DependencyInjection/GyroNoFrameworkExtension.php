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
class GyroNoFrameworkExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setAlias('controller_utils', 'qafoo_labs_noframework.controller_utils');

        if ($config['convert_exceptions']) {
            $container->setParameter('qafoo_labs_noframework.convert_exceptions_map', $config['convert_exceptions']);
        } else {
            $container->removeDefinition('qafoo_labs_noframework.convert_exception_listener');
        }
    }
}
