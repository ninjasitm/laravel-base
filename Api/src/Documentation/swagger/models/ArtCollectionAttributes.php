<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "description", "type", "image", "mediums"}, @SWG\Xml(name="Art"))
 */
class ArtCollectionAttributes
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

    /**
     * @SWG\Property(example="this-is-art")
     *
     * @var string
     */
    public $slug;
}
