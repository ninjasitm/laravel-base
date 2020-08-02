<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="FeatureRelationships"))
 */
class FeatureRelationships
{
    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $image;

    /**
     * @SWG\Property()
     *
     * @var FeatureItem
     */
    public $art;

    /**
     * @SWG\Property()
     *
     * @var FeatureItem
     */
    public $event;

    /**
     * @SWG\Property()
     *
     * @var FeatureItem
     */
    public $user;

    /**
     * @SWG\Property()
     *
     * @var FeatureItem
     */
    public $post;
}
