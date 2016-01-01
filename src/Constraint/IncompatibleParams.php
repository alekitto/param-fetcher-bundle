<?php

namespace Kcs\ParamFetcherBundle\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation()
 */
class IncompatibleParams extends Constraint
{
    public $params;
    public $message;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'params';
    }
}
