<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="RelatedUserRelationships"))
 */
class RelatedUserRelationships
{
    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $avatar;

    /**
     * @SWG\Property()
     *
     * @var Profile
     */
    public $profile;
}
