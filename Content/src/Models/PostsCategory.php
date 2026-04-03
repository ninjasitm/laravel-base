<?php

namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nitm\Content\Models\BaseModel as Model;

/**
 * Class PostsCategory
 *
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property int $category_id
 */
class PostsCategory extends Model
{
    public $table = 'posts_categories';

    public $timestamps = false;

    public $fillable = [
        'category_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'post_id' => 'integer',
        'category_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'category_id' => 'required',
    ];

    /**
     * Post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Post
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }
}
