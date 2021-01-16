<?php

namespace Nitm\Content\Repositories;

use Nitm\Content\Models\User;
use Nitm\Content\Repositories\BaseRepository;
use App\Traits\Repository as RepositoryTrait;
use Nitm\Content\Traits\RepositoryProfile;

/**
 * Class UserRepository
 *
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