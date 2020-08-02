<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "type", "follower", "followee", "start_date", "end_date"}, @SWG\Xml(name="EventAttendeeAttributes"))
 */
class EventAttendeeAttributes
{
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
