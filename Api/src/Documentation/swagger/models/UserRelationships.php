<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="User"))
 */
class UserRelationships
{
    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $avatar;
}
