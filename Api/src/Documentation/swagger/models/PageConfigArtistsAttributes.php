<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigArtists"))
 */
class PageConfigArtistsAttributes
{
    /**
      * @var Artist[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $items;

     /**
      * @var PageConfigArtistsFilters[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $filters;
}
