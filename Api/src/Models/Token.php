<?php

namespace Nitm\Api\Models;

use Model;
use Carbon\Carbon;

/**
 * Model.
 */
class Token extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_api_tokens';

    public $belongsTo = [
      'user' => '\Nitm\Api\Models\User',
   ];

    public $fillable = [
      'user_id',
   ];

    public $visible = [
      'token', 'expires_at',
   ];

    public $jsonable = ['permissions'];

    public function beforeCreate()
    {
        $this->generate();
    }

    public function generate()
    {
        $this->token = str_random(32);
        $this->expires_at = Carbon::now();
        $this->expires_at->addMinutes(Configs::get('tokens_duration'));
        $this->signature = array_get($_COOKIE, 'user_auth', str_random(64));
        $this->ip = \Request::ip();
    }

    public function renew()
    {
        $this->signature = static::getCookieId();
        $this->expires_at = new Carbon($this->expires_at);
        $this->expires_at->addMinutes(Configs::get('tokens_duration'));
        $this->save();
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->diffInSeconds($this->expired_at) < 0;
    }

    public static function findToken($token)
    {
        return static::query()->where(['token' => $token])->with('user')->first();
    }

    public function updateSignature()
    {
        $this->signature = static::getCookieId($this->signature);
        $this->save();
    }

    protected static function getCookieId($default = null)
    {
        return array_get($_COOKIE, 'user_auth', $default);
    }
}
