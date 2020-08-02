<?php

namespace Nitm\Content\Models;

use Model;
use RainLab\User\Models\User as BaseUser;

/**
* Model.
*/
class User extends BaseUser
{
    use \Nitm\Content\Traits\Feature;
    use \Nitm\Content\Traits\Model;
    use \Nitm\Content\Traits\User {
        \Nitm\Content\Traits\User::apiFind insteadof \Nitm\Content\Traits\Model;
        \Nitm\Content\Traits\User::apiQuery insteadof \Nitm\Content\Traits\Model;
    }

    public $table = 'users';

    public $implement = [
        'Nitm.Content.Behaviors.Permissions',
        'Nitm.Content.Behaviors.Follow',
        'Nitm.Content.Behaviors.Search'
    ];

    public $visible = [
        'id', 'username', 'email', 'name', 'surname', 'avatar', 'artCount', 'fullName',
        'apiToken', 'phone', 'mobile', 'company', 'street_addr', 'city', 'zip',
        'categories', 'groups', 'profile', 'country', 'state'
    ];

    public $fillable = [
        'name', 'surname', 'email', 'username', 'fullName',
        'password', 'password_confirmation',
        'iu_gender', 'iu_job', 'iu_about', 'iu_company', 'iu_blog', 'iu_facebook', 'iu_twitter', 'iu_webpage', 'profile'
    ];

    public $with = [
        'avatar', 'apiToken', 'groups', 'country', 'state'
    ];

    public $appends = ['profile', 'fullName'];

    public $eagerWith = [];
    public $attachOne = [
        'avatar' => [
            'Nitm\Content\Models\File',
        ],
    ];

    public $hasOne = [
        'apiToken' => [
            'Nitm\Api\Models\Token',
            'key' => 'user_id',
            'otherKey' => 'id',
        ],
    ];

    public $hasMany = [
        'categories' => [
            'Nitm\Content\Models\AccountCategory',
            'key' => 'id',
            'otherKey' => 'acccount_category_id',
            'through' => 'Nitm\Content\Models\AccountCategoryList',
            'throughKey' => 'id',
        ],
    ];

    /**
    * @var array Relations
    */
    public $belongsToMany = [
        'groups' => ['RainLab\User\Models\UserGroup', 'table' => 'users_groups', 'key' => 'user_id'],
    ];
}
