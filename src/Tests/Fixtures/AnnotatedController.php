<?php

namespace Kcs\ParamFetcherBundle\Tests\Fixtures;

use Kcs\ParamFetcherBundle\Param\QueryParam;
use Kcs\ParamFetcherBundle\Param\RequestParam;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @QueryParam(name="foo")
 */
class AnnotatedController extends Controller
{
    /**
     * @RequestParam(name="barz")
     */
    public function fooAction(ParamFetcherInterface $fetcher)
    {
    }

    public function noAction()
    {
    }
}
