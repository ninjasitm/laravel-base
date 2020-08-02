<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "description", "type", "image", "mediums"}, @SWG\Xml(name="Art"))
 */
class ArtCollection
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

    /**
     * @var string
     * @SWG\Property(example="art")
     */
    public $type;

    /**
     * @var ArtCollectionAttributes
     * @SWG\Property(@SWG\Xml(name="attributes",wrapped=true))
     */
    public $attributes;

    /**
     * @var ArtCollectionRelationships
     * @SWG\Property(@SWG\Xml(name="relationships",wrapped=true))
     */
    public $relationships;
}
