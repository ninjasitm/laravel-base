<?php

namespace Nitm\Content\Models;

use Nitm\Content\Models\BaseModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PostsCategory
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:28 am UTC
 *
 * @property integer $category_id
 */
class PostsCategory extends Model
{
    use SoftDeletes;

    public $table = 'posts_categories';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


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
}