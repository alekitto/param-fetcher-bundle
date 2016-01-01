<?php

namespace Kcs\ParamFetcherBundle\Util;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
trait ResolverTrait
{
    /**
     * @param ContainerInterface $container
     * @param mixed $value
     *
     * @return mixed
     */
    private function resolveValue(ContainerInterface $container, $value)
    {
        if ($container instanceof Container) {
            // Use the container methods if available
            return $container->getParameterBag()->resolveValue($value);
        }

        // Fallback
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolveValue($container, $val);
            }
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $resolved = $container->getParameter($match[1]);
            if (is_string($resolved) || is_numeric($resolved)) {
                return (string) $resolved;
            }

            throw new \RuntimeException(sprintf(
                    'The container parameter "%s" must be a string or a numeric, but it is of type %s.',
                    $match[1],
                    $value,
                    gettype($resolved)
                )
            );
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
