<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="FeedRelationships"))
 */
class FeedRelationships
{
    /**
     * @SWG\Property()
     *
     * @var ActivityUser
     */
    public $actor;

    /**
     * @SWG\Property()
     *
     * @var ActivityObject
     */
    public $object;

    /**
     * @SWG\Property()
     *
     * @var ActivityObject
     */
    public $target;
}
