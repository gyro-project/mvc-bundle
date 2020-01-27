<?php

namespace Gyro\MVC;

/**
 * Wraps a view that is rendering a template with all its data.
 */
class TemplateView
{
    private $viewParams;
    private $actionTemplateName;
    private $statusCode;
    private $headers;

    public function __construct($viewParams, $actionTemplateName = null, $statusCode = 200, array $headers = [])
    {
        $this->viewParams = $viewParams;
        $this->actionTemplateName = $actionTemplateName;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getViewParams() : array
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

    public function getActionTemplateName() : string
    {
        return $this->actionTemplateName;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }
}
