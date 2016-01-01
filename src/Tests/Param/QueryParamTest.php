<?php

namespace Kcs\ParamFetcherBundle\Tests\Param;

use Kcs\ParamFetcherBundle\Param\QueryParam;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class QueryParamTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $query = $this->prophesize(ParameterBag::class);
        $query->get('foo')->shouldBeCalled();

        $request = $this->prophesize(Request::class);
        $request->query = $query->reveal();

        $param = new QueryParam();
        $param->name = 'foo';
        $param->fetch($request->reveal());
    }
}
