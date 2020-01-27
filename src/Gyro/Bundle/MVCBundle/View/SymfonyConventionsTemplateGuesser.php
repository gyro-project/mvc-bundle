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

    public function guessControllerTemplateName(string $controller, ?string $actionName, string $format, string $engine) : string
    {
        [$bundleName, $controllerName, $actionName] = $this->parseControllerCommand($controller, $actionName);

        return $this->createTemplateReference($bundleName, $controllerName, $actionName, $format, $engine);
    }

    private function parseControllerCommand($controller, $actionName = null)
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

    private function createTemplateReference($bundleName, $controllerName, $actionName, $format, $engine)
    {
        if (!$bundleName) {
            return sprintf('%s/%s.%s.%s', $controllerName, $actionName, $format, $engine);
        }

        return sprintf('%s:%s:%s.%s.%s', $bundleName, $controllerName, $actionName, $format, $engine);
    }

    private function extractControllerCallable($controller)
    {
        if (strpos($controller, '::') === false) {
            $controller = $this->parser->parse($controller);
        }

        return explode('::', $controller, 2);
    }

    private function extractControllerName($className)
    {
        if (!preg_match('/([^\\\\]+)Controller$/', $className, $matchController)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', $className));
        }

        return $matchController[1];
    }

    private function extractActionName($method)
    {
        if (!preg_match('/^(.+)Action$/', $method, $matchAction)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $method));
        }

        return $matchAction[1];
    }
}
