<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="RelatedUser"))
 */
class RelatedUser
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
     * @SWG\Property(example="cooluser1")
     *
     * @var string
     */
    public $username;

    /**
     * @SWG\Property(example="cooluser1@example.com")
     *
     * @var string
     */
    public $email;

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
