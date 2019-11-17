<?php

namespace Gyro\Bundle\MVCBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Gyro\Bundle\MVCBundle\DependencyInjection\GyroNoFrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ContainerTest extends TestCase
{
    /**
     * @test
     */
    public function it_compiles_with_container()
    {
        $container = $this->createContainer(array());

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\Controller\ControllerUtils',
            $container->get('qafoo_labs_noframework.controller_utils')
        );

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ViewListener',
            $container->get('qafoo_labs_noframework.view_listener')
        );

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ParamConverterListener',
            $container->get('qafoo_labs_noframework.param_converter_listener')
        );
    }

    /**
     * @test
     */
    public function it_allows_configuring_convert_exceptions()
    {
        $container = $this->createContainer(array(
            'convert_exceptions' => array(
                'foo' => 'bar',
            )
        ));

        $this->assertEquals(array('foo' => 'bar'), $container->getParameter('qafoo_labs_noframework.convert_exceptions_map'));

        $this->assertInstanceOf(
            'Gyro\Bundle\MVCBundle\EventListener\ConvertExceptionListener',
            $container->get('qafoo_labs_noframework.convert_exception_listener')
        );
    }

    public function createContainer(array $config)
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__.'/../../../../' // src dir
        )));

        $loader = new GyroNoFrameworkExtension();
        $container->set('twig', \Phake::mock('Twig\Environment'));
        $container->set('kernel', \Phake::mock('Symfony\Component\HttpKernel\KernelInterface'));
        $container->set('controller_name_converter', \Phake::mock('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser'));
        $container->set('logger', \Phake::mock('Psr\Log\LoggerInterface'));
        $container->set('router', \Phake::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface'));
        $container->registerExtension($loader);
        $loader->load(array($config), $container);

        $container->getCompilerPassConfig()->setRemovingPasses(array());

        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true); // symfony 4 support
        }

        $container->compile();

        return $container;
    }
}
