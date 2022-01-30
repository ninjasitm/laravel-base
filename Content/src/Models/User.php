<?php

namespace Nitm\Content\Models;

use Nitm\Content\Traits\Model;
use Nitm\Content\Traits\Search;
use Nitm\Content\Traits\Feature;
use Illuminate\Notifications\Notifiable;
use Nitm\Content\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 */
class User extends Authenticatable
{
    use Notifiable, Search, Model, Feature, HasFactory;
    use \Nitm\Content\Traits\User {
        \Nitm\Content\Traits\User::apiFind insteadof \Nitm\Content\Traits\Model;
        \Nitm\Content\Traits\User::apiQuery insteadof \Nitm\Content\Traits\Model;
    }

    public $table = 'users';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $visible = [
        'id', 'username', 'email', 'name', 'surname', 'avatar', 'artCount', 'fullName',
        'apiToken', 'phone', 'mobile', 'company', 'street_addr', 'city', 'zip',
        'categories', 'groups', 'profile', 'country', 'state',
    ];

    public $fillable = [
        'name', 'surname', 'email', 'username', 'fullName',
        'password', 'password_confirmation',
        'iu_gender', 'iu_job', 'iu_about', 'iu_company', 'iu_blog', 'iu_facebook', 'iu_twitter', 'iu_webpage', 'profile',
    ];

    public $with = [];

    public $appends = ['profile', 'fullName'];

    public $eagerWith = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return UserFactory::new();
    }
}