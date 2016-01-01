<?php
namespace Kcs\ParamFetcherBundle\Param;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents a parameter passed in POST request properties
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class RequestParam extends Param
{
    /** @var bool */
    public $strict = true;

    /**
     * @inheritDoc
     */
    public function fetch(Request $request)
    {
        return $request->request->get($this->name);
    }
}
