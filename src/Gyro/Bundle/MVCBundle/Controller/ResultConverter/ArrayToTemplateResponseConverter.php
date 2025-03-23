<?php

namespace Gyro\Bundle\MVCBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Gyro\Bundle\MVCBundle\View\TemplateGuesser;
use Gyro\MVC\TemplateView;
use Gyro\MVC\ViewStruct;
use Twig\Environment;
use RuntimeException;

/**
 * Convert array or {@link TemplateView} struct into templated response.
 *
 * Guess the template names with the same algorithm that @Template()
 * in Sensio's FrameworkExtraBundle uses.
 */
class ArrayToTemplateResponseConverter implements ControllerResultConverter
{
    public function __construct(private ?Environment $twig, private TemplateGuesser $guesser, private string $engine)
    {
    }

    /**
     * @param mixed $result
     */
    public function supports($result): bool
    {
        return is_array($result) || $result instanceof TemplateView || $result instanceof ViewStruct;
    }

    /**
     * @param mixed $result
     */
    public function convert($result, Request $request): Response
    {
        $controller = (string) $request->attributes->get('_controller');

        if (is_array($result) || $result instanceof ViewStruct) {
            $result = new TemplateView($result);
        } elseif (! ($result instanceof TemplateView)) {
            throw new RuntimeException(sprintf('Result must be array or TemplateView, %s given', is_object($result) ? get_class($result) : gettype($result)));
        }

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return $this->makeResponseFor(
            $controller,
            $result,
            $request->getRequestFormat() ?: 'html'
        );
    }

    private function makeResponseFor(string $controller, TemplateView $templateView, string $requestFormat): Response
    {
        if ($this->twig === null) {
            throw new RuntimeException('Cannot convert to template response without Twig');
        }

        $viewName = $this->guesser->guessControllerTemplateName(
            $controller,
            $templateView->getActionTemplateName(),
            $requestFormat,
            $this->engine
        );

        return new Response(
            $this->twig->render($viewName, $templateView->getViewParams()),
            $templateView->getStatusCode(),
            $templateView->getHeaders()
        );
    }
}
