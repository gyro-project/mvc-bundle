<?php

namespace Gyro\Bundle\MVCBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Exception;
use ReflectionClass;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Converts Exceptions into Symfony HttpKernel Exceptions before rendering the exception page.
 */
class ConvertExceptionListener
{
    /**
     * @var ?LoggerInterface
     */
    private $logger;

    /**
     * Using the classes works with ::class because it does not trigger the
     * autoloader.
     *
     * @psalm-suppress UndefinedClass
     * @var array<class-string, class-string|int>
     */
    private $exceptionClassMap = [
        Missing404Exception::class => NotFoundHttpException::class,
        EntityNotFoundException::class => NotFoundHttpException::class,
    ];

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
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        $convertedExceptionClass = $this->findConvertToExceptionClass($exception);

        if (!$convertedExceptionClass) {
            return;
        }

        $this->logException($exception);

        $convertedException = $this->convertException($exception, $convertedExceptionClass);
        /** @psalm-suppress UndefinedClass */
        $event->setException($convertedException);
    }

    /**
     * @param string|int $convertToExceptionClass
     *
     * @psalm-param class-string|int $convertToExceptionClass
     */
    private function convertException(Exception $exception, $convertToExceptionClass): Exception
    {
        if (is_numeric($convertToExceptionClass)) {
            return new HttpException((int) $convertToExceptionClass, null, $exception);
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
    private function findConvertToExceptionClass(Exception $exception)
    {
        $exceptionClass = get_class($exception);

        foreach ($this->exceptionClassMap as $originalExceptionClass => $convertedExceptionClass) {
            if ($exceptionClass === $originalExceptionClass || is_subclass_of($exceptionClass, $originalExceptionClass)) {
                return $convertedExceptionClass;
            }
        }

        return null;
    }

    private function logException(Exception $exception): void
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
