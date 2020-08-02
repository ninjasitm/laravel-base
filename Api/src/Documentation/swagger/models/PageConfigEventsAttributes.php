<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigEvents"))
 */
class PageConfigEventsAttributes
{
    /**
      * @var Event[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $items;

     /**
      * @var PageConfigEventsFilters[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $filters;
}
