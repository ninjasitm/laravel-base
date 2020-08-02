<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtMedium"))
 */
class ArtMedium
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
    * @SWG\Property(example="Art Medium")
    *
    * @var string
    */
   public $title;

   /**
    * @SWG\Property(example="art-slug")
    *
    * @var string
    */
   public $slug;

   /**
    * @SWG\Property(example="This is an art medium")
    *
    * @var string
    */
   public $description;
}
