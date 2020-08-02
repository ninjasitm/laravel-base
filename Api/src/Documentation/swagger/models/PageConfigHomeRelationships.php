<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="SellArtConfigRelationships"))
 */
class PageConfigHomeRelationships
{
    /**
     * @var Feature[]
     * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
     */
    public $showcase;

     /**
      * @var Feature[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $showcaseSide;

     /**
      * @var Feed[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $feed;

     /**
      * @var Feed[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $users;

     /**
      * @var Feed[]
      * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
      */
     public $events;
}
