<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigEvents"))
 */
class PageConfigEvents
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

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
