<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Favorite"))
 */
class Favorite
{
    /**
     * @SWG\Property(format="string")
     *
     * @var string
     */
    public $id;
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
     * @var ActivityObject
     */
    public $thing;
}
