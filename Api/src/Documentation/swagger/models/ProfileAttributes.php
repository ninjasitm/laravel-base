<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"user"}, @SWG\Xml(name="ProfileAttributes"))
 */
class ProfileAttributes
{
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
}
