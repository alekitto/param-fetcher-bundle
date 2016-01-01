<?php

namespace Kcs\ParamFetcherBundle\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ParamValidationFailedException extends \Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $errors;

    /**
     * @inheritDoc
     */
    public function __construct(ConstraintViolationListInterface $errors, $message = "", $code = 0, Exception $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getValidationErrors()
    {
        return $this->errors;
    }
}
