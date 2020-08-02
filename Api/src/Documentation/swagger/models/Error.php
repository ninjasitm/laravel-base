<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"method", "reason", "code", "detail"}, @SWG\Xml(name="Error"))
 */
class Error
{
    /**
     * @SWG\Property(format="string",example="GET")
     *
     * @var string
     */
    public $method;

    /**
     * @var string
     * @SWG\Property(example="An error occurred")
     */
    public $reason;

    /**
     * @var int
     * @SWG\Property(example="404",format="int64")
     */
    public $code;

    /**
     * @var object
     * @SWG\Property()
     */
    public $detail;
}
