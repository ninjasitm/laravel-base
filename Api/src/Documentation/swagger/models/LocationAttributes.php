<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"object", "actor", "target", "title", "verb"}, @SWG\Xml(name="Location"))
 */
class LocationAttributes
{
    /**
    * @SWG\Property(example="1412 Broadway Ny, NY")
    *
    * @var string
    */
   public $address;

   /**
    * @SWG\Property(example="Workville")
    *
    * @var string
    */
   public $name;

   /**
    * @SWG\Property(example="New York")
    *
    * @var string
    */
   public $city;

   /**
    * @SWG\Property(example="110018")
    *
    * @var number
    */
   public $zip;

   /**
    * @SWG\Property(example="-73.9865883")
    *
    * @var string
    */
   public $longitude;

   /**
    * @SWG\Property(example="40.7536014")
    *
    * @var string
    */
   public $laitude;

    /**
     * @SWG\Property(example="US")
     *
     * @var string
     */
    public $country_code;

    /**
     * @SWG\Property(example="NY")
     *
     * @var string
     */
    public $state_code;
}
