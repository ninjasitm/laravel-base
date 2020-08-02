<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigCreateEventAttributes"))
 */
class PageConfigCreateEventAttributes
{
    /**
      * @var EventType[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $types;

      /**
       * @var EventCategory[]
       * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
       */
      public $categories;
}
