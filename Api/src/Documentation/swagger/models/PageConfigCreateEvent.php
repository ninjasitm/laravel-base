<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigCreateEvent"))
 */
class PageConfigCreateEvent
{
    /**
    * @SWG\Property(format="int64")
    *
    * @var int
    */
   public $id;

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
