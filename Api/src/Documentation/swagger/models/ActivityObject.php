<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"type", "url", "name", "image"}, @SWG\Xml(name="ActivityObject"))
 */
class ActivityObject
{
    /**
     * @SWG\Property(
     *   format="string",
     *   example="coolart"
     * )
     *
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(example="/art/coolart")
     *
     * @var string
     */
    public $url;

    /**
     * @SWG\Property(example="art")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property(example="This is a cool artwork")
     *
     * @var string
     */
    public $name;

    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $image;
}
