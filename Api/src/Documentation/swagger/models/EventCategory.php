<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="ArtType"))
 */
class EventCategory
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

    /**
     * @SWG\Property(example="event-category")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var CategoryAttributes
     */
    public $attributes;

    /**
     * @SWG\Property()
     *
     * @var CategoryRelationships
     */
    public $relationships;
}
