<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtLocation"))
 */
class ArtLocation
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

   /**
    * @var Image
    * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
    */
   public $image;

   /**
    * @SWG\Property(example="Art Location")
    *
    * @var string
    */
   public $title;

   /**
    * @SWG\Property(example="location-slug")
    *
    * @var string
    */
   public $slug;

   /**
    * @SWG\Property(example="This is an art location")
    *
    * @var string
    */
   public $description;
}
