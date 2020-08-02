<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "description", "attendees", "image", "mediums"}, @SWG\Xml(name="Event"))
 */
class EventAttributes
{
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
}
