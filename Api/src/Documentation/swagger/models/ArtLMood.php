<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtMood"))
 */
class ArtMood
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
    * @SWG\Property(example="Art Mood")
    *
    * @var string
    */
   public $title;

   /**
    * @SWG\Property(example="mood-slug")
    *
    * @var string
    */
   public $slug;

   /**
    * @SWG\Property(example="This is an art mood")
    *
    * @var string
    */
   public $description;
}
