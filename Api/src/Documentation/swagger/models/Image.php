<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"title", "url", "thumb"}, @SWG\Xml(name="Image"))
 */
class Image
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
     * @SWG\Property(example="Large image")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="http://www.mage.com/image.jpg")
     *
     * @var url
     */
    public $url;

    /**
     * @SWG\Property(example="http://www.mage.com/thumbs/image.jpg")
     *
     * @var url
     */
    public $thumb;
}
