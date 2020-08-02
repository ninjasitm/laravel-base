<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtColor"))
 */
class ArtColor
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
    * @SWG\Property(example="Art Color")
    *
    * @var string
    */
   public $title;

   /**
    * @SWG\Property(example="color-slug")
    *
    * @var string
    */
   public $slug;

   /**
    * @SWG\Property(example="This is an art color")
    *
    * @var string
    */
   public $description;
}
