<?php

namespace Nitm\Content\Models;

use Nitm\Content\Models\BaseModel as Model;

/**
 * Class PostsCategory
 *
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property integer $category_id
 */
class PostsCategory extends Model
{
    public $table = 'posts_categories';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['created_at', 'updated_at'];


    public $fillable = [
        'category_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'post_id' => 'integer',
        'category_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'category_id' => 'required'
    ];

    /**
     * Post
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Post
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}