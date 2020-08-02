<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="User"))
 */
class UserAttributes
{
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
}
