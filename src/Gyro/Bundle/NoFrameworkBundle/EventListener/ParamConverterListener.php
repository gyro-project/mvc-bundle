<?php

namespace Gyro\Bundle\NoFrameworkBundle\EventListener;

use Gyro\Bundle\NoFrameworkBundle\ParamConverter\ServiceProviderInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Gyro\Bundle\NoFrameworkBundle\Request\SymfonyFormRequest;
use Gyro\Bundle\NoFrameworkBundle\SymfonyTokenContext;

/**
 * Convert the request parameters into objects when typehinted.
 *
 * This replicates the SensioFrameworkExtraBundle behavior but keeps it simple
 * and without a dependency to allow usage outside Symfony Framework apps
 * (Silex, ..).
 */
class ParamConverterListener
{
    /**
     * @var ServiceProviderInterface
     */
    private $serviceProvider;

    public function __construct(ServiceProviderInterface $container)
    {
        $this->serviceProvider = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        if (\is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif ($controller instanceof \Closure || \method_exists($controller, '__invoke')) {
            $r = new \ReflectionMethod($controller, '__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        // automatically apply conversion for non-configured objects
        foreach ($r->getParameters() as $param) {
            if (!$param->getClass() || $param->getClass()->isInstance($request)) {
                continue;
            }

            $class = $param->getClass()->getName();
            $name = $param->getName();

            if (is_subclass_of($class, "Symfony\\Component\\HttpFoundation\\Session\\SessionInterface") ||
                   $class === "Symfony\\Component\\HttpFoundation\\Session\\SessionInterface") {
                $value = $request->getSession();
            } else if ("Gyro\\MVC\\FormRequest" === $class) {
                $value = new SymfonyFormRequest($request, $this->serviceProvider->getFormFactory());
            } else if ("Gyro\\MVC\\TokenContext" === $class) {
                $value = new SymfonyTokenContext(
                    $this->serviceProvider->getTokenStorage(),
                    $this->serviceProvider->getAuthorizationChecker()
                );
            } else {
                continue;
            }

            $request->attributes->set($name, $value);
        }
    }
}
