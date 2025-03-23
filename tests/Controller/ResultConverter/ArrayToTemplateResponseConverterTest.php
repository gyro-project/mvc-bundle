<?php

namespace Gyro\Bundle\MVCBundle\Tests\Controller\ResultConverter;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\Controller\ResultConverter\ArrayToTemplateResponseConverter;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Gyro\Bundle\MVCBundle\View\TemplateGuesser;
use Gyro\MVC\TemplateView;

class ArrayToTemplateResponseConverterTest extends TestCase
{
    private $twig;
    private $guesser;
    private \Gyro\Bundle\MVCBundle\Controller\ResultConverter\ArrayToTemplateResponseConverter $converter;

    public function setUp() : void
    {
        $this->converter = new ArrayToTemplateResponseConverter(
            $this->twig = \Phake::mock(Environment::class),
            $this->guesser = \Phake::mock(TemplateGuesser::class),
            'twig'
        );
    }

    public function testSupports() : void
    {
        $this->assertTrue($this->converter->supports(new TemplateView(['foo' => 'bar'])));
        $this->assertTrue($this->converter->supports([]));
    }

    public function testRenderArrayToTemplateStringFromController() : void
    {
        $request = new Request();
        $request->attributes->set('_controller', 'ctrl');

        \Phake::when($this->guesser)->guessControllerTemplateName('ctrl', null, 'html', 'twig')->thenReturn('ctrl.html.twig');

        $response = $this->converter->convert(['foo' => 'bar'], $request);

        \Phake::verify($this->twig)->render('ctrl.html.twig', ['foo' => 'bar', 'view' => ['foo' => 'bar']]);
    }
}
