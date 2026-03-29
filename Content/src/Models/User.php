<?php
namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Database\Factories\UserFactory;
use Nitm\Content\Traits\Feature;
use Nitm\Content\Traits\Model;
use Nitm\Content\Traits\Search;

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
class User extends Authenticatable {
    use Notifiable, Search, Model, Feature, HasFactory;
    use \Nitm\Content\Traits\User {
        \Nitm\Content\Traits\User::apiFind insteadof \Nitm\Content\Traits\Model;
        \Nitm\Content\Traits\User::apiQuery insteadof \Nitm\Content\Traits\Model;
    }

    const ROLE_USER   = 'user';
    const ROLE_ADMIN  = 'admin';
    const ROLE_VIEWER = 'viewer';

    public $table = 'users';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        'id',
        'username',
        'email',
        'name',
        'surname',
        'avatar',
        'artCount',
        'fullName',
        'apiToken',
        'phone',
        'mobile',
        'company',
        'street_addr',
        'city',
        'zip',
        'categories',
        'groups',
        'profile',
        'country',
        'state',
    ];

    public $fillable = [
        'name',
        'surname',
        'email',
        'username',
        'fullName',
        'password',
        'password_confirmation',
        'iu_gender',
        'iu_job',
        'iu_about',
        'iu_company',
        'iu_blog',
        'iu_facebook',
        'iu_twitter',
        'iu_webpage',
        'profile',
    ];

    public $with = [];

    public $appends = ['fullName'];

    public $eagerWith = [];

    /**
     * @inheritDoc
     */
    public static function boot() {
        parent::boot();
        static::saving(function ($user) {
            $firstName = Arr::get($user->attributes, 'first_name');
            $lastName  = Arr::get($user->attributes, 'last_name');
            if ($firstName && $lastName) {
                $user->name = "{$firstName} {$lastName}";
            } else {
                $user->name = $user->name ?: "{$firstName} {$lastName}";
            }
            unset($user->attributes['first_name'], $user->attributes['last_name']);
            // dump($firstName, $lastName, $user->name, $user->attributes);
            // $user->username = $user->username ?: $user->email;
        });
        static::creating(function ($user) {
            $user->password = $user->password ?: Str::random(12);
            $user->name     = $user->name ?: "{$user->first_name} {$user->last_name}";
            // $user->username = $user->username ?: $user->email;
        });
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory() {
        return UserFactory::new ();
    }
}