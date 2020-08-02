<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="EventAttendeeRelationships"))
 */
class EventAttendeeRelationships
{
    /**
    * @var User
    * @SWG\Property()
    */
   public $attendee;

   /**
    * @var Event
    * @SWG\Property()
    */
   public $event;
}
