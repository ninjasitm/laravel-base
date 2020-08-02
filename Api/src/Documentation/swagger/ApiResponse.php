<?php

namespace Nitm\Api\Documentation\Swagger;

/**
 * @SWG\Definition(
 *   @SWG\Xml(name="##default")
 * )
 */
class ApiResponse
{
    /**
     * @SWG\Property
     *
     * @var array
     */
    public $data;

    /**
     * @SWG\Property
     *
     * @var array
     */
    public $meta;
}
