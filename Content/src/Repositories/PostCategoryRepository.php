<?php

/**
 * PostCategory Repository
 */

namespace Nitm\Content\Repositories;

use Nitm\Content\Models\PostCategory;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class PostCategoryRepository
 * @package Nitm\Content\Repositories
 * @version July 20, 2020, 1:47 am UTC
 */

class PostCategoryRepository extends BaseRepository
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
        return PostCategory::class;
    }
}
