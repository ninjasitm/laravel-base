<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"image", "mediums"}, @SWG\Xml(name="ArtRelationships"))
 */
class ArtCollectionRelationships
{
    /**
     * @var ArtType
     * @SWG\Property()
     */
    public $art;

    /**
     * @var User
     * @SWG\Property()
     */
    public $author;
}
