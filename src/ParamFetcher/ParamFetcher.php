<?php

namespace Kcs\ParamFetcherBundle\ParamFetcher;

use Kcs\ParamFetcherBundle\Exception\ParamValidationFailedException;
use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\Validator\ParamValidatorInterface;
use Symfony\Component\HttpFoundation\Request;

class ParamFetcher implements ParamFetcherInterface
{
    /**
     * The request this param fetcher belongs to
     *
     * @var Request
     */
    protected $request;

    /**
     * The param validator for request param validation
     *
     * @var ParamValidatorInterface
     */
    protected $validator;

    /**
     * Param instances
     *
     * @var Param[]
     */
    protected $params;

    public function __construct(Request $request, ParamValidatorInterface $validator, array $params)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->params = $params;
    }

    /**
     * @inheritDoc
     */
    public function all($strict = null)
    {
        $resolved = [];
        foreach ($this->params as $name => $param) {
            $resolved[ $name ] = $this->get($name, $strict);
        }

        return $resolved;
    }

    /**
     * @inheritDoc
     */
    public function get($name, $strict = null)
    {
        if (! array_key_exists($name, $this->params)) {
            throw new \InvalidArgumentException("A parameter with name '$name' does not exist");
        }

        $param = $this->params[$name];

        if ($strict === null) {
            $strict = $param->strict;
        }

        $value = $param->fetch($this->request);
        if ($value === null) {
            $value = $param->default;
        }

        $violationList = $this->validator->validate($value, $param);

        if ($violationList->count() > 0) {
            if ($strict) {
                throw new ParamValidationFailedException($violationList);
            }

            $value = $param->default;
        }

        return $value;
    }
}
