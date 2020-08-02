<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "type", "follower", "followee", "start_date", "end_date"}, @SWG\Xml(name="FollowAttributes"))
 */
class FollowAttributes
{
    /**
     * @SWG\Property(example="This is new art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="follow")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var ActivityUser
     */
    public $follower;

    /**
     * @SWG\Property()
     *
     * @var ActivityUser
     */
    public $followee;

    /**
     * @SWG\Property(
     * 	format="dateTime",
     * 	example="2016-09-19T15:47:22"
     * )
     *
     * @var dateTime
     */
    public $start_date;

    /**
     * @SWG\Property(
     * 	format="dateTime",
     * 	example="2016-09-19T15:47:22"
     * )
     *
     * @var dateTime
     */
    public $end_date;
}
