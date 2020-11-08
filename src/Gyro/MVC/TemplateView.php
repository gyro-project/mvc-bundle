<?php

namespace Gyro\MVC;

/**
 * Wraps a view that is rendering a template with all its data.
 */
class TemplateView
{
    /** @var object|array<string,mixed> */
    private $viewParams;
    /** @var ?string */
    private $actionTemplateName;
    /** @var int */
    private $statusCode;
    /** @var array<string,string> */
    private $headers;

    /**
     * @param object|array<string,mixed> $viewParams
     * @param array<string,string>       $headers
     */
    public function __construct($viewParams, ?string $actionTemplateName = null, int $statusCode = 200, array $headers = [])
    {
        $this->viewParams = $viewParams;
        $this->actionTemplateName = $actionTemplateName;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @return array<string,mixed>
     */
    public function getViewParams(): array
    {
        $viewParams = $this->viewParams;

        if (is_object($viewParams)) {
            $viewParams = ['view' => $viewParams];
        }

        if (!isset($viewParams['view'])) {
            $viewParams['view'] = $viewParams;
        }

        return $viewParams;
    }

    public function getActionTemplateName(): ?string
    {
        return $this->actionTemplateName;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
