<?php

namespace Gyro\Bundle\MVCBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class CompatibleTreeBuilder
{
    private ?\Symfony\Component\Config\Definition\Builder\TreeBuilder $symfonyTreeBuilder = null;

    private ?\Symfony\Component\Config\Definition\Builder\NodeDefinition $rootNode = null;

    public function root(string $name): NodeDefinition
    {
        if ($this->symfonyTreeBuilder === null) {
            /** @psalm-suppress TooManyArguments */
            $this->symfonyTreeBuilder = new TreeBuilder($name);
            /** @psalm-suppress UndefinedMethod */
            $this->rootNode = $this->symfonyTreeBuilder->getRootNode();
        }

        if (!($this->rootNode instanceof NodeDefinition)) {
            throw new \RuntimeException("Incompatibale node definition for the root node, must be NodeDefinition.");
        }

        return $this->rootNode;
    }

    public function getTreeBuilder(): TreeBuilder
    {
        if ($this->symfonyTreeBuilder === null) {
            throw new \RuntimeException("No root node was generated for this tree builder.");
        }

        return $this->symfonyTreeBuilder;
    }
}
