<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigHome"))
 */
class PageConfigHomeFilters
{
    /**
      * @var Category[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $categories;
}
