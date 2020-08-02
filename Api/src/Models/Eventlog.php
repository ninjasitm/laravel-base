<?php namespace Nitm\Api\Models;

use Model;


class Eventlog extends Model
{
    protected $table = 'nitm_api_eventlogs';

    protected $guarded = [];

    protected $jsonable = ['exportfields'];

    public function getStatusOptions()
    {
        return [
            '0' => 'Fail',
            '1' => 'OK'
        ];
    }

    public function getStatusAttribute()
    {
        $value = array_get($this->attributes, 'status');

        return array_get($this->getStatusOptions(), $value);
    }
}
