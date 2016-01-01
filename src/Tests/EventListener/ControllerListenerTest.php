<?php

namespace Kcs\ParamFetcherBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Kcs\ParamFetcherBundle\EventListener\ControllerListener;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcherInterface;
use Kcs\ParamFetcherBundle\Reader\Reader;
use Kcs\ParamFetcherBundle\Tests\Fixtures\AnnotatedController;
use Kcs\ParamFetcherBundle\Tests\Fixtures\ObjectController;
use Kcs\ParamFetcherBundle\Validator\ParamValidatorInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);

        $this->validator = $this->prophesize(ParamValidatorInterface::class);
        $this->reader = $this->prophesize(Reader::class);
    }

    public function controllerDataProvider()
    {
        return [
            [ [new AnnotatedController, 'fooAction'], AnnotatedController::class, 'fooAction', 'fetcher' ],
            [ 'Kcs\ParamFetcherBundle\Tests\Fixtures\AnnotatedController::fooAction', AnnotatedController::class, 'fooAction', 'fetcher'],
            [ new ObjectController(), ObjectController::class, '__invoke', 'params' ],
            [ ObjectController::class, ObjectController::class, '__invoke', 'params' ],
        ];
    }

    /**
     * @dataProvider controllerDataProvider
     */
    public function testListenerShouldTryToReadFromCorrectController($controller, $class, $action, $paramName)
    {
        $that = $this;

        $listener = new ControllerListener($this->validator->reveal(), $this->reader->reveal());
        $event = $this->prophesize(FilterControllerEvent::class);
        $event->getController()->willReturn($controller);
        $event->getRequest()->willReturn($this->request->reveal());

        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->set($paramName, Argument::type(ParamFetcherInterface::class))->shouldBeCalled();
        $this->request->attributes = $attributes->reveal();

        $this->reader->get(Argument::type(\ReflectionMethod::class))
            ->will(function($args) use ($that, $class, $action) {
                /** @var \ReflectionMethod $reflector */
                $reflector = $args[0];

                $that->assertEquals($class, $reflector->class);
                $that->assertEquals($action, $reflector->name);
                return [];
            });

        $listener->onKernelController($event->reveal());
    }

    public function testListenerShouldNotInjectIfNoAttributeIsRequired()
    {
        $listener = new ControllerListener($this->validator->reveal(), $this->reader->reveal());
        $event = $this->prophesize(FilterControllerEvent::class);
        $event->getController()->willReturn([new AnnotatedController(), 'noAction']);
        $event->getRequest()->willReturn($this->request->reveal());

        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->set(Argument::cetera())->shouldNotBeCalled();
        $this->request->attributes = $attributes->reveal();

        $listener->onKernelController($event->reveal());
    }
}
