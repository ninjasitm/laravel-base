<?php

namespace Nitm\Content\Models;

use Nitm\Content\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nitm\Content\Traits\Sluggable;

/**
 * Class Post
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property \Nitm\Content\Models\User $user
 * @property integer $user_id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property string $content_html
 * @property string|\Carbon\Carbon $published_at
 * @property boolean $published
 */
class Post extends Model
{
    use SoftDeletes, Sluggable;

    public $table = 'posts';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'content_html',
        'published_at',
        'published'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'title' => 'string',
        'slug' => 'string',
        'excerpt' => 'string',
        'content' => 'string',
        'content_html' => 'string',
        'published_at' => 'datetime',
        'published' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'slug' => 'required',
        'published' => 'required'
    ];

    public $slugs = [
        'slug' => ['title']
    ];
}