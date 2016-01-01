<?php

namespace Kcs\ParamFetcherBundle\Tests\Param;

use Kcs\ParamFetcherBundle\Param\RequestParam;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestParamTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $requestBag = $this->prophesize(ParameterBag::class);
        $requestBag->get('foo')->shouldBeCalled();

        $request = $this->prophesize(Request::class);
        $request->request = $requestBag->reveal();

        $param = new RequestParam();
        $param->name = 'foo';
        $param->fetch($request->reveal());
    }
}
