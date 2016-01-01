<?php

namespace Kcs\ParamFetcherBundle\Tests\EventListener;

use Kcs\ParamFetcherBundle\EventListener\ValidationFailedExceptionListener;
use Kcs\ParamFetcherBundle\Exception\ParamValidationFailedException;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationList;

class ValidationFailedExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidationFailedExceptionListener
     */
    private $listener;

    public function setUp()
    {
        $this->listener = new ValidationFailedExceptionListener();
    }

    public function testListenerShouldReturnNullOnGenericExceptions()
    {
        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->willReturn(new \Exception());
        $event->setException(Argument::cetera())->shouldNotBeCalled();

        $this->listener->onKernelException($event->reveal());
    }

    public function testListenerShouldReturnBadRequestException()
    {
        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->willReturn(new ParamValidationFailedException(new ConstraintViolationList()));
        $event->setException(Argument::type(BadRequestHttpException::class))->shouldBeCalled();

        $this->listener->onKernelException($event->reveal());
    }
}
