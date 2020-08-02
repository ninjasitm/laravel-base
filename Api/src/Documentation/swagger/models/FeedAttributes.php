<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Feed"))
 */
class FeedAttributes
{
    /**
     * @SWG\Property(example="This is new art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="Join")
     *
     * @var string
     */
    public $verb;
}
