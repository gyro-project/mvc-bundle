<?php

namespace Gyro\MVC;

use Symfony\Component\HttpFoundation\Response;

class RedirectRoute
{
    /** @var string */
    private $routeName;
    /** @var array<string,string|int|float|bool|null> */
    private $parameters;
    /** @var ?Response */
    private $response;

    /** @var int */
    private $statusCode = 302;

    /**
     * @param array<string,string|int|float|bool|null> $parameters
     * @param Response|int $response
     */
    public function __construct(string $routeName, array $parameters = [], $response = null)
    {
        $this->routeName = $routeName;
        $this->parameters = $parameters;

        if (is_int($response)) {
            $this->statusCode = $response;
        } else if ($response instanceof Response || $response === null) {
            $this->response = $response;
        } else {
            throw new \InvalidArgumentException(sprintf(
                '$response must be of type int|Response|null, %s given',
                is_object($response) ? get_class($response) : gettype($response)
            ));
        }
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @return array<string,string|int|float|bool|null>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
