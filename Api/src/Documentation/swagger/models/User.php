<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="User"))
 */
class User
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
     * @SWG\Property(example="user")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var UserAttributes
     */
    public $attributes;

    /**
     * @SWG\Property()
     *
     * @var UserRelationships
     */
    public $relationships;
}
