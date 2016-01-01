<?php

namespace Kcs\ParamFetcherBundle\Validator;

use Kcs\ParamFetcherBundle\Constraint\IncompatibleParams;
use Kcs\ParamFetcherBundle\Param\Param;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParamValidator implements ParamValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, Param $param)
    {
        $constraint = $this->getRequirementsConstraint($value, $param);

        if (null !== $constraint) {
            $constraint = [$constraint];

            if ($param->allowBlank === false) {
                $constraint[] = new NotBlank();
            }
            if ($param->nullable === false) {
                $constraint[] = new NotNull();
            }
        } else {
            $constraint = [];
        }

        if ($param->incompatibles) {
            $constraint[] = new IncompatibleParams($param->incompatibles);
        }

        if (! count($constraint)) {
            return new ConstraintViolationList();
        }

        return $this->validator->validate($value, $constraint);
    }

    private function getRequirementsConstraint($value, Param $param)
    {
        if (null === $param->requirements || ($value === $param->default && $param->default !== null)) {
            return null;
        }

        $constraint = $param->requirements;
        if (is_string($constraint)) {
            $constraint = new Regex([
                'pattern' => '#^' . $constraint . '$#xsu',
                'message' => sprintf(
                    "%s parameter value '%s' does not match requirements '%s'",
                    $param->name,
                    $value,
                    $param->requirements
                )
            ]);
        } elseif (is_array($constraint) && isset($constraint['rule']) && isset($constraint['error_message'])) {
            $constraint = new Regex([
                'pattern' => '#^' . $constraint['rule'] . '$#xsu',
                'message' => $constraint['error_message']
            ]);
        }

        return $constraint;
    }
}
