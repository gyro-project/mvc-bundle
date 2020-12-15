<?php

namespace Gyro\Bundle\MVCBundle\View;

use Gyro\Bundle\MVCBundle\Controller\GyroControllerNameParser;

/**
 * Guess Templates based on Symfony and SensioFrameworkExtra conventions
 */
class SymfonyConventionsTemplateGuesser implements TemplateGuesser
{
    /**
     * @var BundleLocation
     */
    private $bundleLocation;

    /**
     * @var \Gyro\Bundle\MVCBundle\Controller\GyroControllerNameParser
     */
    private $parser;

    public function __construct(BundleLocation $bundleLocation, GyroControllerNameParser $parser)
    {
        $this->bundleLocation = $bundleLocation;
        $this->parser = $parser;
    }

    public function guessControllerTemplateName(string $controller, ?string $actionName, string $format, string $engine): string
    {
        [$bundleName, $controllerName, $actionName] = $this->parseControllerCommand($controller, $actionName);

        return $this->createTemplateReference((string) $bundleName, (string) $controllerName, (string) $actionName, $format, $engine);
    }

    /**
     * @return array<int,string|null>
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    private function parseControllerCommand(string $controller, ?string $actionName = null): array
    {
        [$className, $method] = $this->extractControllerCallable($controller);

        $bundleName     = $this->bundleLocation->locationFor($className);
        $controllerName = $this->extractControllerName($className);

        if ($actionName === null) {
            $actionName = $this->extractActionName($method);
        }

        if (strpos($className, "App\\Controller") === 0) {
            return ['', $controllerName, $actionName];
        }

        return [$bundleName, $controllerName, $actionName];
    }

    private function createTemplateReference(string $bundleName, string $controllerName, ?string $actionName, string $format, string $engine): string
    {
        if (!$bundleName) {
            return sprintf('%s/%s.%s.%s', $controllerName, (string) $actionName, $format, $engine);
        }

        return sprintf('%s:%s:%s.%s.%s', $bundleName, $controllerName, (string) $actionName, $format, $engine);
    }

    /** @return array<int,string> */
    private function extractControllerCallable(string $controller): array
    {
        if (strpos($controller, '::') === false) {
            $controller = $this->parser->parse($controller);
        }

        return explode('::', $controller, 2);
    }

    private function extractControllerName(string $className): string
    {
        if (!preg_match('/([^\\\\]+)Controller$/', $className, $matchController)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', $className));
        }

        return $matchController[1];
    }

    private function extractActionName(string $method): string
    {
        if (!preg_match('/^(.+)Action$/', $method, $matchAction)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $method));
        }

        return $matchAction[1];
    }
}
