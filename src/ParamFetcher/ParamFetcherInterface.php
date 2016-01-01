<?php

namespace Kcs\ParamFetcherBundle\ParamFetcher;

interface ParamFetcherInterface
{
    /**
     * Get all the request parameters
     *
     * @param bool $strict Throw exception if validation fails
     *
     * @return array
     */
    public function all($strict = false);

    /**
     * Get request parameter
     *
     * @param $name
     * @param bool $strict Throw exception if validation fails
     *
     * @return mixed
     */
    public function get($name, $strict = false);
}
