<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "url", "thumb"}, @SWG\Xml(name="Image"))
 */
class FeatureItem
{
    /**
     * @SWG\Property(
     *   format="int64",
     *   example="image/1"
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @SWG\Property(example="This is a featured item!")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="item-slug")
     *
     * @var string
     */
    public $slug;

    /**
     * @SWG\Property(example="Hre's a description for this feature")
     *
     * @var string
     */
    public $description;

    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $image;
}
