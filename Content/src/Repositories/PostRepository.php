<?php

/**
 * Post Repository
 */

namespace Nitm\Content\Repositories;

use Nitm\Content\Models\Post;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class PostRepository
 * @package Nitm\Content\Repositories
 * @version July 20, 2020, 1:45 am UTC
 */

class PostRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Post::class;
    }
}
