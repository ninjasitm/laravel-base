<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigArt"))
 */
class PageConfigArt
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

   /**
    * @var Art[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $items;

   /**
    * @var PageConfigArtFilters[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $filters;
}
