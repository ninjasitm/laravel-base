<?php

/**
 * Category Repository
 */

namespace Nitm\Content\Repositories;

use Nitm\Content\Models\Category;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class CategoryRepository
 * @package Nitm\Content\Repositories
 * @version July 20, 2020, 1:44 am UTC
 */

class CategoryRepository extends BaseRepository
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
        return Category::class;
    }
}
