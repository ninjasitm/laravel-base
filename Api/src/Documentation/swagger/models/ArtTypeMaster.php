<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtType"))
 */
class ArtTypeMaster
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

   /**
    * @var ArtTypeMaster[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $types;

   /**
    * @var ArtMood[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $moods;

   /**
    * @var ArtColor[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $colors;

   /**
    * @var ArtLocation[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $locations;
}
