<?php

namespace Gyro\Bundle\MVCBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new CompatibleTreeBuilder();
        $rootNode = $treeBuilder->root('gyro_mvc');

        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->arrayNode('convert_exceptions')
                    ->useAttributeAsKey('original')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $treeBuilder->getTreeBuilder();
    }
}
