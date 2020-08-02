<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "slug"}, @SWG\Xml(name="CategoryAttributes"))
 */
class CategoryAttributes
{
    /**
     * @SWG\Property(example="Art Medium")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="art-slug")
     *
     * @var string
     */
    public $slug;

    /**
     * @SWG\Property(example="This is an art medium")
     *
     * @var string
     */
    public $description;
}
