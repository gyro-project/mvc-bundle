<?php

namespace Gyro\MVC;

use Symfony\Component\HttpFoundation\Response;

class RedirectRoute
{
    private string $routeName;
    /** @var array<string,string|int|float|bool|null> */
    private array $parameters;
    /** @var ?Response */
    private $response;

    private int $statusCode = 302;

    /**
     * @param array<string,string|int|float|bool|null> $parameters
     * @param Response|int|null                        $response
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function __construct(string $routeName, array $parameters = [], $response = null)
    {
        $this->routeName = $routeName;
        $this->parameters = $parameters;

        if (is_int($response)) {
            $this->statusCode = $response;
        } elseif ($response instanceof Response || $response === null) {
            $this->response = $response;
        } else {
            throw new \InvalidArgumentException(sprintf(
                '$response must be of type int|Response|null, %s given',
                get_debug_type($response)
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
