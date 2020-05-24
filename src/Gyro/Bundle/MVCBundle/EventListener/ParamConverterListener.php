<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Gyro\Bundle\MVCBundle\ParamConverter\ServiceProvider;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Gyro\Bundle\MVCBundle\Request\SymfonyFormRequest;
use Gyro\Bundle\MVCBundle\SymfonyTokenContext;
use Gyro\MVC\FormRequest;
use Gyro\MVC\TokenContext;

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
     * @var ServiceProvider
     */
    private $serviceProvider;

    public function __construct(ServiceProvider $container)
    {
        $this->serviceProvider = $container;
    }

    /**
     * @param $event FilterControllerEvent|ControllerEvent
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyNullReference
     */
    public function onKernelController($event) : void
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

            if (is_subclass_of($class, SessionInterface::class) ||
                   $class === SessionInterface::class) {
                $value = $request->getSession();
            } elseif ($class === FormRequest::class) {
                $value = new SymfonyFormRequest($request, $this->serviceProvider->getFormFactory());
            } elseif ($class === TokenContext::class) {
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
