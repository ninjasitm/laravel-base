<?php

namespace Nitm\Content\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nitm\Content\Models\BaseModel as Model;
use Nitm\Content\Traits\Sluggable;
use Nitm\Content\Traits\NestedTree;

/**
 * Class PostCategory
 *
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property string $name
 * @property string $slug
 * @property string $code
 * @property string $description
 * @property integer $parent_id
 * @property integer $nest_left
 * @property integer $nest_right
 * @property integer $nest_depth
 */
class PostCategory extends Model
{
    use Sluggable, NestedTree;

    public $table = 'post_categories';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = [];

    public $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'parent_id',
        'nest_left',
        'nest_right',
        'nest_depth'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'code' => 'string',
        'description' => 'string',
        'parent_id' => 'integer',
        'nest_left' => 'integer',
        'nest_right' => 'integer',
        'nest_depth' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];

    protected $slugs = [
        'slug' => ['title'],
    ];
}
