<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"login", "password"}, @SWG\Xml(name="Account"))
 */
class Account
{
    /**
     * @SWG\Property(example="test")
     *
     * @var string
     */
    public $login;

    /**
     * @SWG\Property(example="testpasword")
     *
     * @var string
     */
    public $password;
}
