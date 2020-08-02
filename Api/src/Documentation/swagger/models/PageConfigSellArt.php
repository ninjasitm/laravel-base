<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigSellArt"))
 */
class PageConfigSellArt
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

  /**
   * @var ArtTypeMaster
   * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
   */
  public $types;

   /**
    * @var ArtColor[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $colors;

   /**
    * @var ArtMood[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $moods;

   /**
    * @var ArtCollection[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $collections;
}
