<?php

namespace Kcs\ParamFetcherBundle\Tests\Reader;

use Doctrine\Common\Annotations\AnnotationReader;
use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\Reader\Reader;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetShouldOnlyReturnParams()
    {
        $annotationReader = $this->prophesize(AnnotationReader::class);

        $method_annots = [];
        $class_annots = [];

        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $method_annots[] = $param;

        $param = $this->prophesize(Param::class);
        $param->name = 'bar';
        $method_annots[] = $param;

        $annot = new \stdClass();
        $method_annots[] = $annot;

        $param = $this->prophesize(Param::class);
        $param->name = 'foo_c';
        $class_annots[] = $param;

        $param = $this->prophesize(Param::class);
        $param->name = 'bar_c';
        $class_annots[] = $param;

        $annot = new \stdClass();
        $method_annots[] = $annot;

        $annotationReader->getClassAnnotations(Argument::type(\ReflectionClass::class))
            ->willReturn($class_annots);
        $annotationReader->getMethodAnnotations(Argument::type(\ReflectionMethod::class))
            ->willReturn($method_annots);

        $container = $this->prophesize(ContainerInterface::class);
        $container->getParameter(Argument::type('string'))
            ->will(function ($arguments) { return $arguments[0]; });

        $reader = new Reader($annotationReader->reveal());
        $reader->setContainer($container->reveal());

        $params = $reader->get(new \ReflectionMethod(__CLASS__, 'setUp'));

        $this->assertCount(4, $params);
        $this->assertContainsOnlyInstancesOf(Param::class, $params);
    }

    public function testParamsShouldNotBeResolvedTwice()
    {
        $annotationReader = $this->prophesize(AnnotationReader::class);

        $method_annots = [];

        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->requirements = '%param1%';
        $method_annots[] = $param;

        $annotationReader->getClassAnnotations(Argument::type(\ReflectionClass::class))
            ->willReturn([]);
        $annotationReader->getMethodAnnotations(Argument::type(\ReflectionMethod::class))
            ->willReturn($method_annots);

        $container = $this->prophesize(ContainerInterface::class);
        $container->getParameter('param1')
            ->shouldBeCalledTimes(1)
            ->will(function () { return 'bar'; });

        $reader = new Reader($annotationReader->reveal());
        $reader->setContainer($container->reveal());

        $reader->get(new \ReflectionMethod(__CLASS__, 'setUp'));
        $reader->get(new \ReflectionMethod(__CLASS__, 'setUp'));
    }
}
