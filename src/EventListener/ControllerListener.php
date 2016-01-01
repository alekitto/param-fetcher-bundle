<?php

namespace Kcs\ParamFetcherBundle\EventListener;

use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcher;
use Kcs\ParamFetcherBundle\ParamFetcher\ParamFetcherInterface;
use Kcs\ParamFetcherBundle\Reader\ReaderInterface;
use Kcs\ParamFetcherBundle\Validator\ParamValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListener
{
    /**
     * Parameter validator
     *
     * @var ParamValidatorInterface
     */
    protected $validator;

    /**
     * Param reader
     *
     * @var ReaderInterface
     */
    private $reader;

    public function __construct(ParamValidatorInterface $validator, ReaderInterface $reader)
    {
        $this->validator = $validator;
        $this->reader = $reader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $reflector = $this->getReflector($controller);

        if (! $reflector instanceof \ReflectionMethod) {
            // Support for closures or function controllers is not implemented
            // as the annotation reader does not support functions
            return;
        }

        $attributeName = $this->getAttributeName($reflector);
        if ($attributeName === null) {
            return;
        }

        $params = $this->reader->get($reflector);

        $fetcher = $this->createParamFetcher($request, $params);
        $request->attributes->set($attributeName, $fetcher);
    }

    /**
     * Create a new ParamFetcher instance
     *
     * @param Request $request
     * @param Param[] $params
     *
     * @return ParamFetcherInterface
     */
    protected function createParamFetcher(Request $request, array $params)
    {
        return new ParamFetcher($request, $this->validator, $params);
    }

    /**
     * Get the attribute name for ParamFetcher injection
     *
     * @param \ReflectionFunctionAbstract $controllerReflector
     *
     * @return null|string
     */
    private function getAttributeName(\ReflectionFunctionAbstract $controllerReflector)
    {
        foreach ($controllerReflector->getParameters() as $parameter) {
            $hintedClass = $parameter->getClass();
            if ($hintedClass !== null && $hintedClass->implementsInterface(ParamFetcherInterface::class)) {
                return $parameter->getName();
            }
        }

        return null;
    }

    /**
     * Get a reflection object from a controller callable
     *
     * @param callable $controller
     *
     * @return \ReflectionFunctionAbstract The reflection object
     */
    private function getReflector($controller)
    {
        if ($controller instanceof \Closure) {
            return new \ReflectionFunction($controller);
        }

        if (is_array($controller)) {
            list ($class, $method) = $controller;

            return new \ReflectionMethod($class, $method);
        }

        if (is_string($controller) && strpos($controller, '::') !== false) {
            return new \ReflectionMethod($controller);
        }

        if (is_string($controller) && function_exists($controller)) {
            return new \ReflectionFunction($controller);
        }

        if (is_object($controller) && method_exists($controller, '__invoke')) {
            return new \ReflectionMethod(get_class($controller), '__invoke');
        }

        if (is_string($controller) && class_exists($controller) && method_exists($controller, '__invoke')) {
            return new \ReflectionMethod($controller, '__invoke');
        }

        throw new \InvalidArgumentException(
            (is_object($controller) ? get_class($controller) : (is_string($controller) ? $controller : gettype($controller))) .
            ' is not a valid controller callable'
        );
    }
}
