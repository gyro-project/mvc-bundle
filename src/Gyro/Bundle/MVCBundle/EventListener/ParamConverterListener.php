<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Gyro\Bundle\MVCBundle\ParamConverter\ServiceProvider;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Gyro\Bundle\MVCBundle\Request\SymfonyFormRequest;
use Gyro\Bundle\MVCBundle\SymfonyTokenContext;
use Gyro\MVC\FormRequest;
use Gyro\MVC\TokenContext;
use Technically\CallableReflection\CallableReflection;

/**
 * Convert the request parameters into objects when typehinted.
 *
 * This replicates the SensioFrameworkExtraBundle behavior but keeps it simple
 * and without a dependency to allow usage outside Symfony Framework apps
 * (Silex, ..).
 */
class ParamConverterListener
{
    public function __construct(private ServiceProvider $serviceProvider)
    {
    }

    /**
     * @param ControllerEvent|ControllerEvent $event
     *
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress PossiblyNullReference
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();
        /** @psalm-suppress UndefinedClass */
        $request = $event->getRequest();

        $r = CallableReflection::fromCallable($controller);

        // automatically apply conversion for non-configured objects
        foreach ($r->getParameters() as $param) {
            if (!$param->getTypes()) {
                continue;
            }

            $types = $param->getTypes();

            // skip union and intersection types (for now?)
            if (count($types) > 1) {
                continue;
            }

            $class = $types[0]->getType();
            $name = $param->getName();

            if (
                is_subclass_of($class, SessionInterface::class) ||
                   $class === SessionInterface::class
            ) {
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
