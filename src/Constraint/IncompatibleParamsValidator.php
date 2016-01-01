<?php

namespace Kcs\ParamFetcherBundle\Constraint;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IncompatibleParamsValidator extends ConstraintValidator
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @inheritDoc
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (! $constraint instanceof IncompatibleParams) {
            throw new UnexpectedTypeException($constraint, IncompatibleParams::class);
        }

        $params = (array)$constraint->params;
        $request = $this->requestStack->getCurrentRequest();

        foreach ($params as $incompatible) {
            if ($request->query->has($incompatible) || $request->request->has($incompatible)) {
                if ($this->context instanceof ExecutionContextInterface) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ param }}', $incompatible);
                } else {
                    $this->buildViolation($constraint->message)
                        ->setParameter('{{ param }}', $incompatible);
                }
            }
        }
    }
}
