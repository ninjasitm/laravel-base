<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "description", "type", "image", "mediums"}, @SWG\Xml(name="Art"))
 */
class ArtAttributes
{
    /**
     * @SWG\Property(example="This is art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="This is art")
     *
     * @var string
     */
    public $description;
}
