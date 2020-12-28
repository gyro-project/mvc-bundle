<?php

namespace Gyro\Bundle\MVCBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\DependencyInjection\GyroMVCExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function it_compiles_with_container() : void
    {
        $container = $this->createContainer([]);

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ViewListener',
            $container->get('gyro_mvc.view_listener')
        );

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ParamConverterListener',
            $container->get('gyro_mvc.param_converter_listener')
        );

        $this->assertFalse($container->has('gyro_mvc.turbolinks_listener'));
    }

    /**
     * @test
     */
    public function it_allows_configuring_convert_exceptions() : void
    {
        $container = $this->createContainer([
            'convert_exceptions' => ['foo' => 'bar'],
        ]);

        $this->assertEquals(['foo' => 'bar'], $container->getParameter('gyro_mvc.convert_exceptions_map'));

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ConvertExceptionListener',
            $container->get('gyro_mvc.convert_exception_listener')
        );
    }

    public function createContainer(array $config)
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug'       => false,
            'kernel.bundles'     => [],
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__ . '/../../../../', // src dir
        ]));

        $loader = new GyroMVCExtension();
        $container->set('twig', \Phake::mock('Twig\Environment'));
        $container->set('kernel', \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'));
        $container->set('controller_name_converter', \Phake::mock('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser'));
        $container->set('logger', \Phake::mock('Psr\Log\LoggerInterface'));
        $container->set('router', \Phake::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface'));
        $container->set('event_dispatcher', \Phake::mock(EventDispatcherInterface::class));
        $container->registerExtension($loader);
        $loader->load([$config], $container);

        $container->getCompilerPassConfig()->setRemovingPasses([]);

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true); // symfony 4 support
        }

        $container->compile();

        return $container;
    }
}
