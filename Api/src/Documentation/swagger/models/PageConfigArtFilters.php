<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigArt"))
 */
class PageConfigArtFilters
{
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
