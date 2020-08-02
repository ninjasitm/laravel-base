<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Feature"))
 */
class FeatureAttributes
{
    /**
     * @SWG\Property(example="This is new art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="This is a description")
     *
     * @var string
     */
    public $description;

    /**
     * @SWG\Property()
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var bool
     */
    public $isActive;
}
