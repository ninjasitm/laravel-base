<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="LocationRelationships"))
 */
class LocationRelationships
{
    /**
    * @var Image
    * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
    */
   public $image;
}
