<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"image", "mediums"}, @SWG\Xml(name="ArtRelationships"))
 */
class ArtRelationships
{
    /**
     * @var ArtType
     * @SWG\Property()
     */
    public $type;

    /**
     * @var User
     * @SWG\Property()
     */
    public $author;

    /**
     * @var Image
     * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
     */
    public $image;

    /**
     * @var ArtMedium[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $mediums;
}
