<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"id", "type", "attributes", "relationships"}, @SWG\Xml(name="Event"))
 */
class Event
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;
    /**
     * @SWG\Property(example="2016-09-22 00:00:00")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="This is an event")
     *
     * @var string
     */
    public $description;

    /**
     * @SWG\Property(example="This is an event")
     *
     * @var dateTime
     */
    public $created_at;

    /**
     * @SWG\Property(example="2016-09-22 00:00:00")
     *
     * @var dateTime
     */
    public $updated_at;

    /**
     * @SWG\Property(example="2016-09-22 00:00:00")
     *
     * @var dateTime
     */
    public $starts_at;

    /**
     * @SWG\Property(example="2016-09-22 00:00:00")
     *
     * @var dateTime
     */
    public $ends_at;

    /**
     * @SWG\Property(example="postponed")
     *
     * @var string
     */
    public $status;

    /**
     * @SWG\Property(example="false")
     *
     * @var bool
     */
    public $is_free;

    /**
     * @SWG\Property(example="100.00")
     *
     * @var string
     */
    public $cost;
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
