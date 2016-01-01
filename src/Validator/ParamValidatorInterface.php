<?php

namespace Kcs\ParamFetcherBundle\Validator;

use Kcs\ParamFetcherBundle\Param\Param;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ParamValidatorInterface
{
    /**
     * Validate a value against a param requirements
     *
     * @param $value
     * @param Param $param
     *
     * @return ConstraintViolationListInterface
     */
    public function validate($value, Param $param);
}
