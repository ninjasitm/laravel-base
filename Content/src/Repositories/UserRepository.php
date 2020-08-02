<?php

namespace Nitm\Content\Repositories;

use Nitm\Content\User;
use Nitm\Content\Repositories\BaseRepository;

/**
 * Class UserRepository
 * @package Nitm\Content\Repositories
 */

class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'password'
    ];

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
        return User::class;
    }
}
