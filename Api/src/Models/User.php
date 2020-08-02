<?php

namespace Nitm\Api\Models;

use Model;

/**
* Model.
*/
class User extends \Nitm\Content\Models\RelatedUser
{
    public $visible = [
        'id', 'username', 'email', 'name', 'surname', 'fullName',
        'street_addr', 'zip', 'phone',
        'profile', 'avatar', 'apiToken', 'country', 'state'
    ];
    
    public $fillable = [
        'id', 'username', 'email', 'name', 'surname', 'fullName',
        'profile', 'avatar', 'apiToken', 'password', 'password_confirmation'
    ];
    
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
    
    public function attributesToArray()
    {
        $result = parent::attributesToArray();
        unset($result['api_token']);
        
        return $result;
    }
}
