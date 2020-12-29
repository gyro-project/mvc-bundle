<?php

namespace Gyro\Bundle\MVCBundle\DependencyInjection;

use Gyro\MVC\SymfonyVersion;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class CompatibleTreeBuilder
{
    /** @var TreeBuilder|null */
    private $symfonyTreeBuilder;

    /** @var NodeDefinition|null */
    private $rootNode;

    public function root(string $name): NodeDefinition
    {
        if ($this->symfonyTreeBuilder === null) {
            if (SymfonyVersion::isVersion4Dot4AndAbove()) {
                /** @psalm-suppress TooManyArguments */
                $this->symfonyTreeBuilder = new TreeBuilder($name);
                /** @psalm-suppress UndefinedMethod */
                $this->rootNode = $this->symfonyTreeBuilder->getRootNode();
            } else {
                /** @psalm-suppress TooFewArguments */
                $this->symfonyTreeBuilder = new TreeBuilder();
                /** @psalm-suppress UndefinedMethod */
                $this->rootNode = $this->symfonyTreeBuilder->root($name);
            }
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
