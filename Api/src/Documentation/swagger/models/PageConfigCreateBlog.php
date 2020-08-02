<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigCreateBlog"))
 */
class PageConfigCreateBlog
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

      /**
       * @var Post[]
       * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
       */
      public $items;

     /**
      * @var PageConfigBlogFilters[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $filters;
}
