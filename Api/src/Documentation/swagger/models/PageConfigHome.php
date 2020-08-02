<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(@SWG\Xml(name="PageConfigHome"))
 */
class PageConfigHome
{
    /**
     * @SWG\Property(format="int64")
     *
     * @var int
     */
    public $id;

    /**
     * @var string
     * @SWG\Property(example="config-home")
     */
    public $type;

    /**
     * @var PageConfigHomeAttributes
     * @SWG\Property(@SWG\Xml(name="attributes",wrapped=true))
     */
    public $attributes;

    /**
     * @var PageConfigHomeRelationships
     * @SWG\Property(@SWG\Xml(name="relationships",wrapped=true))
     */
    public $relationships;
}
