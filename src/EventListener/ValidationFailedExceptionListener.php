<?php

namespace Kcs\ParamFetcherBundle\EventListener;

use Kcs\ParamFetcherBundle\Exception\ParamValidationFailedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidationFailedExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (! $exception instanceof ParamValidationFailedException) {
            return;
        }

        $event->setException($this->convertException($exception));
    }

    private function convertException(ParamValidationFailedException $exception)
    {
        // @todo
        return new BadRequestHttpException();
    }
}
