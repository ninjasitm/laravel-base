<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Feature"))
 */
class Feature
{
    /**
     * @SWG\Property(format="string")
     *
     * @var string
     */
    public $id;

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
