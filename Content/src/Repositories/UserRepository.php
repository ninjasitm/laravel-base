<?php

namespace Nitm\Content\Repositories;

use Nitm\Content\Models\User;
use Nitm\Content\NitmContent;
use Nitm\Content\Repositories\BaseRepository;
use Nitm\Content\Traits\RepositoryProfile;

/**
 * Class UserRepository
 *
 * @package Nitm\Content\Repositories
 */

class UserRepository extends BaseRepository
{
    use RepositoryProfile;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        $class = NitmContent::userModel();
        return $class;
    }
}