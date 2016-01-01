<?php

namespace Kcs\ParamFetcherBundle\Reader;

use Kcs\ParamFetcherBundle\Param\Param;

interface ReaderInterface
{
    /**
     * Get params from configuration
     *
     * @param \ReflectionFunctionAbstract $controllerReflector The reflection for the current controller
     *
     * @return Param[]
     */
    public function get(\ReflectionFunctionAbstract $controllerReflector);
}
