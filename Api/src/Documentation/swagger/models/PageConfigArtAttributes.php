<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigArt"))
 */
class PageConfigArtAttributes
{
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
