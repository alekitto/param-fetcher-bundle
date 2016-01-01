<?php

namespace Kcs\ParamFetcherBundle\Tests\Constraint;

use Kcs\ParamFetcherBundle\Constraint\IncompatibleParams;
use Kcs\ParamFetcherBundle\Constraint\IncompatibleParamsValidator;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IncompatibleParamsValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->request = $this->prophesize(Request::class);

        $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());
        $this->validator = new IncompatibleParamsValidator($this->requestStack->reveal());

        $violationBuilder = $this->prophesize(ConstraintViolationBuilderInterface::class);

        $this->context = $this->prophesize(ExecutionContextInterface::class);
        $this->context->buildViolation(Argument::cetera())->willReturn($violationBuilder->reveal());
        $this->validator->initialize($this->context->reveal());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateThrowIfInvalidConstraintIsPassed()
    {
        $this->validator->validate('', new NotBlank());
    }

    public function testValidateChecksQueryAndRequestAttributes()
    {
        $requestBag = $this->prophesize(ParameterBag::class);
        $queryBag = $this->prophesize(ParameterBag::class);
        $queryBag->has(Argument::type('string'))->willReturn(true, false)->shouldBeCalled();
        $requestBag->has(Argument::type('string'))->willReturn(false, true)->shouldBeCalled();

        $this->request->query = $queryBag->reveal();
        $this->request->request = $requestBag->reveal();

        $this->validator->validate('', new IncompatibleParams(['params' => ['foo', 'bar']]));
    }

    public function testValidateBuildViolationPre25()
    {
        if (! method_exists($this->validator, 'buildViolation')) {
            $this->markTestSkipped('Skipping test of removed functionality');
            return;
        }

        $requestBag = $this->prophesize(ParameterBag::class);
        $queryBag = $this->prophesize(ParameterBag::class);
        $queryBag->has(Argument::type('string'))->willReturn(true);
        $requestBag->has(Argument::type('string'))->willReturn(true);

        $this->request->query = $queryBag->reveal();
        $this->request->request = $requestBag->reveal();

        $this->context = $this->prophesize('Symfony\Component\Validator\ExecutionContextInterface');
        $this->context->getValue()->shouldBeCalled();
        $this->validator->initialize($this->context->reveal());
        $this->validator->validate('', new IncompatibleParams(['params' => ['foo', 'bar']]));
    }
}
