<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"image", "mediums"}, @SWG\Xml(name="EventRelationships"))
 */
class EventRelationships
{
    /**
     * @var EventType
     * @SWG\Property()
     */
    public $type;

    /**
     * @var User
     * @SWG\Property()
     */
    public $author;

     /**
      * @var EventCategory
      * @SWG\Property()
      */
     public $category;

    /**
     * @var Image
     * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
     */
    public $image;

    /**
     * @var EventAttendee[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $attendees;

    /**
     * @var Location[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $location;
}
