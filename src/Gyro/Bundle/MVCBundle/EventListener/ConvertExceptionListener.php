<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Psr\Log\LoggerInterface;
use Throwable;
use ReflectionClass;

/**
 * Converts Exceptions into Symfony HttpKernel Exceptions before rendering the exception page.
 */
class ConvertExceptionListener
{
    private ?\Psr\Log\LoggerInterface $logger;

    /**
     * Using the classes works with ::class because it does not trigger the
     * autoloader.
     *
     * @psalm-suppress UndefinedClass
     * @var array<class-string, class-string|int>
     */
    private array $exceptionClassMap;

    /**
     * @param array<string,string> $exceptionClassMap
     *
     * @psalm-param array<class-string,class-string|int> $exceptionClassMap
     */
    public function __construct(?LoggerInterface $logger = null, array $exceptionClassMap = [])
    {
        $this->logger = $logger;
        $this->exceptionClassMap = $exceptionClassMap;
    }

    /**
     * @param ExceptionEvent|GetResponseForExceptionEvent $event
     */
    public function onKernelException($event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        $convertedExceptionClass = $this->findConvertToExceptionClass($exception);

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if (!$convertedExceptionClass) {
            return;
        }

        $this->logException($exception);

        $convertedException = $this->convertException($exception, $convertedExceptionClass);
        /** @psalm-suppress UndefinedClass */
        $event->setThrowable($convertedException);
    }

    /**
     * @param string|int $convertToExceptionClass
     *
     * @psalm-param class-string|int $convertToExceptionClass
     */
    private function convertException(Throwable $exception, $convertToExceptionClass): Throwable
    {
        if (is_numeric($convertToExceptionClass)) {
            return new HttpException((int) $convertToExceptionClass, '', $exception);
        }

        $reflectionClass = new ReflectionClass($convertToExceptionClass);
        $constructor = $reflectionClass->getConstructor();

        if (! $constructor) {
            return $reflectionClass->newInstance();
        }

        $args = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->getName() === 'message') {
                $args[] = $exception->getMessage();
            } elseif ($parameter->getName() === 'code') {
                $args[] = $exception->getCode();
            } elseif ($parameter->getName() === 'previous') {
                $args[] = $exception;
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                return new HttpException(500);
            }
        }

        return $reflectionClass->newInstanceArgs($args);
    }

    /**
     * @return string|int|null
     *
     * @psalm-return class-string|int|null
     */
    private function findConvertToExceptionClass(Throwable $exception)
    {
        $exceptionClass = get_class($exception);

        foreach ($this->exceptionClassMap as $originalExceptionClass => $convertedExceptionClass) {
            if ($exceptionClass === $originalExceptionClass || is_subclass_of($exceptionClass, $originalExceptionClass)) {
                return $convertedExceptionClass;
            }
        }

        return null;
    }

    private function logException(Throwable $exception): void
    {
        if ($this->logger === null) {
            return;
        }

        $message = sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $this->logger->critical($message, ['exception' => $exception]);
    }
}
