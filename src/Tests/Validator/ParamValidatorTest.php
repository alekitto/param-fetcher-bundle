<?php

namespace Kcs\ParamFetcherBundle\Tests\Validator;

use Kcs\ParamFetcherBundle\Constraint\IncompatibleParams;
use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\Validator\ParamValidator;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParamValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $validator;

    /**
     * @var ParamValidator
     */
    private $paramValidator;

    public function setUp()
    {
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->paramValidator = new ParamValidator($this->validator->reveal());
    }

    public function valuesDataProvider()
    {
        return [
            [null],
            [''],
            ['http://localhost/'],
            ['some_string']
        ];
    }

    /**
     * @dataProvider valuesDataProvider
     */
    public function testValidateShouldReturnEmptyErrorListIfNoRequirementsAreSet($value)
    {
        $param = $this->prophesize(Param::class);
        $param->requirements = null;

        $list = $this->paramValidator->validate($value, $param->reveal());
        $this->assertEmpty($list);
    }

    /**
     * @dataProvider valuesDataProvider
     */
    public function testValidateShouldReturnEmptyErrorListIfValueIsEqualsToDefault($value)
    {
        if (null === $value) {
            $this->markTestSkipped("Invalid test if value is null");
            return;
        }

        $param = $this->prophesize(Param::class);
        $param->default = $value;
        $param->requirements = new \stdClass();

        $list = $this->paramValidator->validate($value, $param->reveal());
        $this->assertEmpty($list);
    }

    public function testValidateConvertsRequirementsStringToRegexConstraint()
    {
        $param = $this->prophesize(Param::class);
        $param->requirements = '[a-z]+';

        $that = $this;
        $this->validator->validate(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use ($that) {
                $constraints = $args[1];
                $that->assertNotEmpty($constraints);

                $constraint = $constraints[0];
                $that->assertInstanceOf(Regex::class, $constraint);
                $that->assertContains('[a-z]+', $constraint->pattern);
            });

        $this->paramValidator->validate('', $param->reveal());
    }

    public function testValidateConvertsRequirementsArrayToRegexConstraint()
    {
        $param = $this->prophesize(Param::class);
        $param->requirements = ['rule' => '[a-z]+', 'error_message' => 'test message'];

        $that = $this;
        $this->validator->validate(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use ($that) {
                $constraints = $args[1];
                $that->assertNotEmpty($constraints);

                $constraint = $constraints[0];
                $that->assertInstanceOf(Regex::class, $constraint);
                $that->assertContains('[a-z]+', $constraint->pattern);
                $that->assertContains('test message', $constraint->message);
            });

        $this->paramValidator->validate('', $param->reveal());
    }

    public function testValidateChecksForBlankValues()
    {
        $param = $this->prophesize(Param::class);
        $param->requirements = '.+';
        $param->nullable = true;
        $param->allowBlank = false;
        $param->incompatibles = [];

        $that = $this;
        $this->validator->validate(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use ($that) {
                $constraints = $args[1];
                $that->assertNotEmpty($constraints);

                $constraint = $constraints[1];
                $that->assertInstanceOf(NotBlank::class, $constraint);
            });

        $this->paramValidator->validate('', $param->reveal());
    }

    public function testValidateChecksForNullValues()
    {
        $param = $this->prophesize(Param::class);
        $param->requirements = '.+';
        $param->nullable = false;
        $param->allowBlank = true;
        $param->incompatibles = [];

        $that = $this;
        $this->validator->validate(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use ($that) {
                $constraints = $args[1];
                $that->assertNotEmpty($constraints);

                $constraint = $constraints[1];
                $that->assertInstanceOf(NotNull::class, $constraint);
            });

        $this->paramValidator->validate('foo', $param->reveal());
    }

    public function testValidateChecksForIncompatibilities()
    {
        $param = $this->prophesize(Param::class);
        $param->allowBlank = true;
        $param->nullable = true;
        $param->incompatibles = ['foo', 'bar'];

        $that = $this;
        $this->validator->validate(Argument::type('string'), Argument::type('array'))
            ->shouldBeCalledTimes(1)
            ->will(function ($args) use ($that) {
                $constraints = $args[1];
                $that->assertNotEmpty($constraints);

                $constraint = $constraints[0];
                $that->assertInstanceOf(IncompatibleParams::class, $constraint);
            });

        $this->paramValidator->validate('foo', $param->reveal());
    }
}
