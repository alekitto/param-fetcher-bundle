<?php

namespace Kcs\ParamFetcherBundle\Tests\ParamFetcher;

use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcher;
use Kcs\ParamFetcherBundle\Validator\ParamValidator;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ParamFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->validator = $this->prophesize(ParamValidator::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetShouldThrowIfNonExistentParamNameIsRequested()
    {
        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), []);
        $fetcher->get('non-existent');
    }

    public function testGetShouldCallValidation()
    {
        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->fetch(Argument::type(Request::class))->willReturn('bar');

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), ['foo' => $param->reveal()]);

        $this->validator->validate('bar', Argument::any())
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList());

        $fetcher->get('foo');
    }

    public function testGetShouldSetDefaultIfFetchReturnNull()
    {
        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->default = 'bar';
        $param->fetch(Argument::type(Request::class))->willReturn(null);

        $this->validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), ['foo' => $param->reveal()]);
        $this->assertEquals('bar', $fetcher->get('foo'));
    }

    public function testGetShouldSetDefaultIfValidationFails()
    {
        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->requirements = 'bar';
        $param->default = 'bar';

        $violationList = $this->prophesize(ConstraintViolationList::class);
        $violationList->count()->willReturn(1);
        $this->validator->validate(Argument::cetera())->willReturn($violationList->reveal());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), ['foo' => $param->reveal()]);
        $this->assertEquals('bar', $fetcher->get('foo', false));
    }

    /**
     * @expectedException \Kcs\ParamFetcherBundle\Exception\ParamValidationFailedException
     */
    public function testGetShouldThrowIfValidationFailsAndStrictIsTrue()
    {
        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->requirements = 'bar';
        $param->default = 'bar';

        $violationList = $this->prophesize(ConstraintViolationList::class);
        $violationList->count()->willReturn(1);
        $this->validator->validate(Argument::cetera())->willReturn($violationList->reveal());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), ['foo' => $param->reveal()]);
        $fetcher->get('foo', true);
    }

    /**
     * @expectedException \Kcs\ParamFetcherBundle\Exception\ParamValidationFailedException
     */
    public function testGetShouldInheritStrictIfNotSpecifiedAsArgument()
    {
        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->requirements = 'bar';
        $param->default = 'bar';
        $param->strict = true;

        $violationList = $this->prophesize(ConstraintViolationList::class);
        $violationList->count()->willReturn(1);
        $this->validator->validate(Argument::cetera())->willReturn($violationList->reveal());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), ['foo' => $param->reveal()]);
        $fetcher->get('foo');
    }

    public function testAllShouldReturnAllParams()
    {
        $params = [];

        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $params['foo'] = $param->reveal();

        $param = $this->prophesize(Param::class);
        $param->name = 'bar';
        $params['bar'] = $param->reveal();

        $param = $this->prophesize(Param::class);
        $param->name = 'barz';
        $params['barz'] = $param->reveal();

        $violationList = $this->prophesize(ConstraintViolationList::class);
        $violationList->count()->willReturn(0);
        $this->validator->validate(Argument::cetera())->willReturn($violationList->reveal());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), $params);

        $all = $fetcher->all();
        $this->assertCount(3, $all);
        $this->assertEquals(['foo', 'bar', 'barz'], array_keys($all));
    }

    public function testFetchReturnArrayIfParamIsArray()
    {
        $params = [];

        $param = $this->prophesize(Param::class);
        $param->name = 'foo';
        $param->array = true;
        $param->fetch(Argument::cetera())->willReturn(1);
        $params['foo'] = $param->reveal();

        $violationList = $this->prophesize(ConstraintViolationList::class);
        $violationList->count()->willReturn(0);
        $this->validator->validate(Argument::type('array'), Argument::cetera())->willReturn($violationList->reveal());

        $fetcher = new ParamFetcher($this->request->reveal(), $this->validator->reveal(), $params);

        $fetched = $fetcher->get('foo');
        $this->assertInternalType('array', $fetched);
    }
}
