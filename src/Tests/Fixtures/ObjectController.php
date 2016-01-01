<?php

namespace Kcs\ParamFetcherBundle\Tests\Fixtures;

use Kcs\ParamFetcherBundle\Param\QueryParam;
use Kcs\ParamFetcherBundle\Param\RequestParam;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcherInterface;

/**
 * @QueryParam(name="fooz")
 */
class ObjectController
{
    /**
     * @RequestParam(name="bar")
     */
    public function __invoke(ParamFetcherInterface $params)
    {
    }
}
