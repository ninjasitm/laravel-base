<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"url", "type", "name", "displayName", "image"}, @SWG\Xml(name="ActivityUser"))
 */
class ActivityUser
{
    /**
     * @SWG\Property(
     *   format="string",
     *   example="cooluser"
     * )
     *
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(example="/user/cooluser")
     *
     * @var string
     */
    public $url;

    /**
     * @SWG\Property(example="user")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property(example="cooluser")
     *
     * @var string
     */
    public $name;

    /**
     * @SWG\Property(example="Cool User")
     *
     * @var string
     */
    public $displayName;

    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $image;
}
