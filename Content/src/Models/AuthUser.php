<?php

namespace Nitm\Content\Models;

use Model;

/**
 * Model.
 */
class AuthUser extends User
{
    public $with = ['avatar'];
    public $implement = [
      'Nitm.Content.Behaviors.Search',
      'Nitm.Content.Behaviors.Permissions',
   ];
    public $eagerWith = [];

    public $visible = [
      'id', 'username', 'email', 'name', 'avatar', 'apiToken',
   ];

    public $appends = [];

    public function attributesToArray()
    {
        $result = parent::attributesToArray();
        unset($result['api_token']);

        return $result;
    }

    public function getMorphClass()
    {
        return 'RainLab\User\Models\User';
    }
}
