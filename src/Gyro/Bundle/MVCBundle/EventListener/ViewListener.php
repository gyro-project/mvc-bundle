<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Gyro\Bundle\MVCBundle\Controller\ResultConverter\ControllerResultConverter;
use Gyro\Bundle\MVCBundle\Controller\ResultConverter\ControllerYieldApplier;
use Generator;

/**
 * Converts non Response results into various side effects from a controller.
 */
class ViewListener
{
    /** @var ControllerResultConverter[] */
    private $converters = [];

    /** @var ControllerYieldApplier[] */
    private $yieldAppliers = [];

    public function addConverter(ControllerResultConverter $converter) : void
    {
        $this->converters[] = $converter;
    }

    public function addYieldApplier(ControllerYieldApplier $applier) : void
    {
        $this->yieldAppliers[] = $applier;
    }

    /**
     * @param ViewEvent|GetResponseForControllerResultEvent $event
     */
    public function onKernelView($event) : void
    {
        $request = $event->getRequest();

        if (! $request->attributes->has('_controller')) {
            return;
        }

        $controller = (string) $request->attributes->get('_controller');
        /** @psalm-suppress UndefinedClass */
        $result = $event->getControllerResult();

        if (!$controller || $result instanceof Response) {
            return;
        }

        $response = $result instanceof Generator
            ? $this->unrollGenerator($result, $request)
            : $this->convert($result, $request);

        /** @psalm-suppress UndefinedClass */
        $event->setResponse($response);
    }

    private function unrollGenerator(Generator $generator, Request $request) : Response
    {
        /** @var array<object,array> $yields */
        $yields = iterator_to_array($generator);

        $result = $generator->getReturn();
        \assert(\is_object($result) || \is_array($result));

        if (!$result) {
            throw new \LogicException("Controllers with generators must return a result that is or can be converted to a Response.");
        }

        $response = $this->convert($result, $request);

        foreach ($yields as $yield) {
            /** @var mixed $yield */
            foreach ($this->yieldAppliers as $applier) {
                if ($applier->supports($yield)) {
                    $applier->apply($yield, $request, $response);
                }
            }
        }

        return $response;
    }

    /**
     * @param mixed $result
     */
    private function convert($result, Request $request) : Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        foreach ($this->converters as $converter) {
            if ($converter->supports($result)) {
                return $converter->convert($result, $request);
            }
        }

        throw new \RuntimeException(sprintf(
            'Could not convert type "%s" into a Response object. No converter found.',
            is_object($result) ? get_class($result) : gettype($result)
        ));
    }
}
