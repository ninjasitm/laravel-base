<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Feed"))
 */
class Feed
{
    /**
     * @SWG\Property(format="string")
     *
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(example="This is new art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="Join")
     *
     * @var string
     */
    public $verb;

   /**
    * @SWG\Property()
    *
    * @var ActivityUser
    */
   public $actor;

   /**
    * @SWG\Property()
    *
    * @var ActivityObject
    */
   public $object;

   /**
    * @SWG\Property()
    *
    * @var ActivityObject
    */
   public $target;
}
