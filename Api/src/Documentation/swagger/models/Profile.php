<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"user"}, @SWG\Xml(name="Profile"))
 */
class Profile
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
    * @SWG\Property(example="This is the cooluser1's biography")
    *
    * @var string
    */
   public $bio;

   /**
    * @SWG\Property(example="This is the cooluser1's short biography")
    *
    * @var string
    */
   public $bio_short;

    /**
     * @SWG\Property()
     *
     * @var RelatedUser
     */
    public $user;
}
