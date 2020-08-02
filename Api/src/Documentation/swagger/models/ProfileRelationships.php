<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"user"}, @SWG\Xml(name="ProfileRelationships"))
 */
class ProfileRelationships
{
    /**
     * @SWG\Property()
     *
     * @var User
     */
    public $user;
}
