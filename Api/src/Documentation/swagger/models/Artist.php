<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="Artist"))
 */
class Artist
{
    /**
     * @SWG\Property(
     *   format="string",
     *   example="cooluser"
     * )
     *
     * @var string
     */
    public $id;

    /**
     * @SWG\Property(example="user")
     *
     * @var string
     */
    public $type;

    /**
     * @SWG\Property()
     *
     * @var ArtistAttributes
     */
    public $attributes;

    /**
     * @SWG\Property()
     *
     * @var ArtistRelationships
     */
    public $relationships;
}
