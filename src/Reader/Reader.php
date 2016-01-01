<?php

namespace Kcs\ParamFetcherBundle\Reader;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Kcs\ParamFetcherBundle\Param\Param;
use Kcs\ParamFetcherBundle\Util\ResolverTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Reader implements ReaderInterface, ContainerAwareInterface
{
    use ResolverTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritDoc
     */
    public function get(\ReflectionFunctionAbstract $controllerReflector)
    {
        if (! $controllerReflector instanceof \ReflectionMethod) {
            throw new \InvalidArgumentException("Reading annotations from functions other than class methods is not supported yet");
        }

        $methodAnnots = $this->annotationReader->getMethodAnnotations($controllerReflector);
        $classAnnots = $this->annotationReader->getClassAnnotations($controllerReflector->getDeclaringClass());
        $annots = array_merge($classAnnots, $methodAnnots);

        $params = [];
        foreach ($annots as $annotation) {
            if (! $annotation instanceof Param) {
                continue;
            }

            $this->resolve($annotation);
            $params[ $annotation->name ] = $annotation;
        }

        return $params;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function resolve(Param $param)
    {
        if ($param->resolved) {
            return;
        }

        $param->default = $this->resolveValue($this->container, $param->default);
        $param->incompatibles = $this->resolveValue($this->container, $param->incompatibles);
        $param->strict = $this->resolveValue($this->container, $param->strict);

        if (is_string($param->requirements)) {
            $param->requirements = $this->resolveValue($this->container, $param->requirements);
        }

        $param->resolved = true;
    }
}
