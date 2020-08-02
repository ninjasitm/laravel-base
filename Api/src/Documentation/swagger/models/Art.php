<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "description", "type", "image", "mediums"}, @SWG\Xml(name="Art"))
 */
class Art
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

   /**
    * @SWG\Property(example="This is art")
    *
    * @var string
    */
   public $title;

   /**
    * @SWG\Property(example="This is art")
    *
    * @var string
    */
   public $description;

    /**
     * @var ArtType
     * @SWG\Property()
     */
    public $type;

    /**
     * @var User
     * @SWG\Property()
     */
    public $author;

    /**
     * @var Image
     * @SWG\Property(@SWG\Xml(name="image",wrapped=true))
     */
    public $image;

    /**
     * @var ArtMedium[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $mediums;
}
