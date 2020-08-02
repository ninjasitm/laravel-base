<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="EventAttendee"))
 */
class EventAttendee
{
    /**
     * @SWG\Property(format="string")
     *
     * @var string
     */
    public $id;

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
    /**
     * @SWG\Property(
     * 	format="dateTime",
     * 	example="2016-09-19T15:47:22"
     * )
     *
     * @var dateTime
     */
    public $created_at;

    /**
     * @SWG\Property(
     * 	format="string",
     * 	example="going"
     * )
     *
     * @var string
     */
    public $status;
}
