<?php
namespace Kcs\ParamFetcherBundle\Param;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents a parameter passed in GET request properties
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class QueryParam extends Param
{
    /**
     * @inheritDoc
     */
    public function fetch(Request $request)
    {
        return $request->query->get($this->name);
    }
}
