<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="PostRelationships"))
 */
class PostRelationships
{
    /**
     * @var Category[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $categories;

     /**
      * @var Image[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $featuredImages;

      /**
       * @var Image[]
       * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
       */
      public $contentImages;
}
