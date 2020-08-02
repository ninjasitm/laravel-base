<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="CategoryRelationships"))
 */
class CategoryRelationships
{
    /**
     * @var Image
     * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
     */
    public $image;
}
