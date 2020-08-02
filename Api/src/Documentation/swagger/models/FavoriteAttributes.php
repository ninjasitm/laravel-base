<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "type", "thing"}, @SWG\Xml(name="FavoriteAttributes"))
 */
class FavoriteAttributes
{
    /**
     * @SWG\Property(example="This is new art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="favorite")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var ActivityUser
     */
    public $user;

    /**
     * @SWG\Property()
     *
     * @var object
     */
    public $thing;
}
