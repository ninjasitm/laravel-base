<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Post"))
 */
class Post
{
    /**
    * @SWG\Property(format="string")
    *
    * @var string
    */
   public $id;
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

   /**
    * @var Category[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $categories;

   /**
    * @var Image[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $featuredImages;

   /**
    * @var Image[]
    * @SWG\Property(@SWG\Xml(name="tag",wrapped=true))
    */
   public $contentImages;
}
