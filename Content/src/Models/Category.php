<?php

namespace Nitm\Content\Models;

use Database\Factories\Nitm\Content\Models\CategoryFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Nitm\Content\Models\BaseModel as Model;
use Nitm\Content\Traits\NestedTree;
use Nitm\Content\Traits\Sluggable;

/**
 * Class Category
 *
 * @package Nitm\Content\Models
 * @version July 20, 2020, 1:25 am UTC
 *
 * @property \Nitm\Content\Models\User $author
 * @property \Nitm\Content\Models\User $editor
 * @property \Nitm\Content\Models\User $deleter
 * @property \Illuminate\Database\Eloquent\Collection $projects
 * @property \Illuminate\Database\Eloquent\Collection $people
 * @property \Illuminate\Database\Eloquent\Collection $project3s
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $photo_url
 * @property integer $author_id
 * @property integer $editor_id
 * @property integer $deleter_id
 * @property integer $parent_id
 * @property integer $nest_left
 * @property integer $nest_right
 * @property integer $nest_depth
 */

/**
 * @SWG\Definition(
 *      definition="Category",
 *      required={"author_id"},
 * @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="title",
 *          description="title",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="slug",
 *          description="slug",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="description",
 *          description="description",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="photo_url",
 *          description="photo_url",
 *          type="string"
 *      ),
 * @SWG\Property(
 *          property="author_id",
 *          description="author_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="editor_id",
 *          description="editor_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 * @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 * @SWG\Property(
 *          property="deleter_id",
 *          description="deleter_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="parent_id",
 *          description="parent_id",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="nest_left",
 *          description="nest_left",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="nest_right",
 *          description="nest_right",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="nest_depth",
 *          description="nest_depth",
 *          type="integer",
 *          format="int32"
 *      ),
 * @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class Category extends Model
{
    use SoftDeletes, Sluggable, NestedTree;

    public $table = 'categories';

    public $bindToType;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'title',
        'slug',
        'description',
        'photo_url',
        'author_id',
        'editor_id',
        'deleter_id',
        'parent_id',
        'nest_left',
        'nest_right',
        'nest_depth',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'title'       => 'string',
        'slug'        => 'string',
        'description' => 'string',
        'photo_url'   => 'string',
        'author_id'   => 'integer',
        'editor_id'   => 'integer',
        'deleter_id'  => 'integer',
        'parent_id'   => 'integer',
        'nest_left'   => 'integer',
        'nest_right'  => 'integer',
        'nest_depth'  => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title'       => 'required',
        'description' => 'sometimes',
        'author_id'   => 'sometimes',
    ];

    protected $slugs = [
        'slug' => ['title', 'parentSlugTitle'],
    ];

    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new \Nitm\Content\Database\Scopes\CategoryDefaultOrderScope());

        static::saving(
            function ($model) {
                if (!$model->slug) {
                    $model->slugAttributes();
                }
            }
        );
    }

    /**
     * @return [type]
     */
    public function getBelongsToOtherAttribute()
    {
        return !is_null(array_get($this->attributes, 'parent_id'));
    }

    /**
     * @param mixed $value
     *
     * @return [type]
     */
    public function setBelongsToOtherAttribute($value)
    {
        if (!$value) {
            $this->attributes['parent_id'] = null;
        }
    }

    /**
     * @param mixed $value
     *
     * @return [type]
     */
    public function setEditorIdAttribute($value)
    {
        if (!$value) {
            $this->attributes['editor_id'] = null;
        }
    }

    /**
     * @return [type]
     */
    public function getParentSlugTitleAttribute()
    {
        $parent = !is_object($this->parent) ? $this->parent()->first() : $this->parent;
        if (is_object($parent) && $parent->id == $this->id) {
            $parent = $this->parent()->first();
        }
        if (is_object($parent) && $parent->slug != 'base-category' && $parent->id != $this->id) {
            return $parent->title;
        }

        return '';
    }

    public function getParentAttribute()
    {
        if ($this->relationLoaded('parent')) {
            return $this->getRelation('parent');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function author()
    {
        return $this->belongsTo(\Nitm\Content\Models\User::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function editor()
    {
        return $this->belongsTo(\Nitm\Content\Models\User::class, 'editor_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function deleter()
    {
        return $this->belongsTo(\Nitm\Content\Models\User::class, 'deleter_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function projects()
    {
        return $this->hasMany(\Nitm\Content\Models\Project::class, 'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function people()
    {
        return $this->hasMany(\Nitm\Content\Models\Person::class, 'position_id');
    }

    /**
     * Scopes
     */

    /**
     * @param mixed $query
     *
     * @return [type]
     */
    public function scopeSelf($query)
    {
        $slug = isset($this->_is) ? $this->_is : str_replace('_', '-', snake_case(class_basename(get_called_class())));
        if ($slug != 'category') {
            $query->where(
                [
                    'slug' => $slug,
                ]
            );
        }

        return $query;
    }

    /**
     * @param mixed $query
     *
     * @return [type]
     */
    public function scopeBindToType($query)
    {
        if (get_called_class() !== 'Nitm\Content\Models\Category') {
            if (!$this->id) {
                $slug  = isset($this->_is) ? $this->_is : str_replace('_', '-', Str::snake(class_basename(get_called_class())));
                $model = \DB::table($this->getTable())->where(
                    [
                        'slug' => $slug,
                    ]
                )->first();
                if ($model) {
                    $this->fill((array) $model);
                    $query->allChildren();
                }
            }
        }
    }

    public function newQuery()
    {
        $query = parent::newQuery();
        if ($this->bindToType && !$this->id) {
            $query->bindToType();
        }
        return $query;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return CategoryFactory::new ();
    }
}
