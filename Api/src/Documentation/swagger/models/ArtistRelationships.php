<?php

namespace Nitm\Api\Documentation\Swagger\Models;

/**
 * @SWG\Definition(required={"username", "email"}, @SWG\Xml(name="Artist"))
 */
class ArtistRelationships
{
    /**
     * @SWG\Property()
     *
     * @var Image
     */
    public $avatar;

    /**
     * @SWG\Property()
     *
     * @var Profile
     */
    public $profile;

    /**
     * @SWG\Property()
     *
     * @var Art[]
     */
    public $art;

    /**
     * @SWG\Property()
     *
     * @var int
     */
    public $art_count;

    /**
     * @SWG\Property()
     *
     * @var int
     */
    public $follower_count;
}
