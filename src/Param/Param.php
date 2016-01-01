<?php

namespace Kcs\ParamFetcherBundle\Param;
use Symfony\Component\HttpFoundation\Request;

/**
 * The abstract param class
 */
abstract class Param
{
    /**
     * @Required()
     * @var string
     */
    public $name;

    /** @var string */
    public $key = null;

    /** @var mixed */
    public $requirements = null;

    /** @var mixed */
    public $default = null;

    /** @var string */
    public $description;

    /** @var bool */
    public $strict = false;

    /** @var bool */
    public $array = false;

    /** @var bool */
    public $nullable = false;

    /** @var bool */
    public $allowBlank = true;

    /** @var array */
    public $incompatibles = array();

    /**
     * This indicates whether this param values are already resolved
     * with container parameters values
     * Do not use it in your annotations!
     *
     * @var bool
     * @internal
     */
    public $resolved = false;

    /**
     * Fetch the parameter from the request
     *
     * @param Request $request
     *
     * @return mixed
     */
    abstract public function fetch(Request $request);
}
