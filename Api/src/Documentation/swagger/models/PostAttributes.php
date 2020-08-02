<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Post"))
 */
class PostAttributes
{
    /**
     * @SWG\Property(example="This is art")
     *
     * @var string
     */
    public $title;

    /**
     * @SWG\Property(example="this-is-a-post")
     *
     * @var string
     */
    public $slug;

    /**
     * @SWG\Property(example="This post has stuff written in it")
     *
     * @var string
     */
    public $excerpt;

    /**
     * @SWG\Property(example="This is the stuff witten about this post")
     *
     * @var string
     */
    public $content;
}
