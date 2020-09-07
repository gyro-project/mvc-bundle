<?php

namespace Gyro\MVC;

use Symfony\Component\HttpFoundation\Response;

class RedirectRoute
{
    /** @var string */
    private $routeName;
    /** @var array<string,string|int|float> */
    private $parameters;
    /** @var ?Response */
    private $response;

    /**
     * @param array<string,string|int|float> $parameters
     */
    public function __construct(string $routeName, array $parameters = [], Response $response = null)
    {
        $this->routeName = $routeName;
        $this->parameters = $parameters;
        $this->response = $response;
    }

    public function getRouteName() : string
    {
        return $this->routeName;
    }

    /**
     * @return array<string,string|int|float>
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    public function getResponse() : ?Response
    {
        return $this->response;
    }
}
