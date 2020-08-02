<?php

namespace Nitm\Content\Models;

use Model;
use Nitm\Restful\Classes\Trivet;

/**
 * Model.
 */
class Category extends BaseContent
{
    use \Nitm\Content\Traits\Category;
    use \October\Rain\Database\Traits\NestedTree;
    use \October\Rain\Database\Traits\Sluggable;

    public $bindToType;

    /*
     * Validation
     */
    public $rules = [];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_categories';

    protected $slugs = [
        'slug' => ['title', 'parentSlugTitle'],
    ];

    public $visible = [
        'id', 'title', 'slug', 'image', 'description', 'children',
    ];

    public $with = ['image'];

    public $eagerWith = [
        'image', 'author',
    ];

    public $fillable = [
        'id', 'title', 'image', 'slug', 'description', 'nest_depth', 'nest_left', 'nest_right', 'parent_id',
    ];

    public $implement = [
        '@Nitm.Content.Behaviors.Blamable',
        '@Nitm.Content.Behaviors.Search',
        '@Nitm.Content.Behaviors.Permissions',
        '@Nitm.Content.Behaviors.Activity',
    ];

    public $attachOneDefault = [
        'image' => [
            'Nitm\Content\Models\File',
            'attachment_type' => 'Nitm\Content\Models\Category',
        ],
    ];

    public $attachManyDefault = [
        'images' => [
            'Nitm\Content\Models\File',
            'morphClass' => 'Nitm\Content\Models\Category',
        ],
    ];

    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new \Nitm\Content\Scopes\CategoryDefaultOrderScope());
    }

    public function beforeSave()
    {
        $this->slug = null;
        $this->slugAttributes();
    }

    public function getBelongsToOtherAttribute()
    {
        return !is_null(array_get($this->attributes, 'parent_id'));
    }

    public function setBelongsToOtherAttribute($value)
    {
        if (!$value) {
            $this->attributes['parent_id'] = null;
        }
    }

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
     * Custom API query function.
     *
     * @param array   $options  Array of parameters for the query builder
     * @param bool    $multiple Is this a request for multiple records?
     * @param Builder $query    The query to use
     *
     * @return [type] [description]
     */
    public static function apiQuery($options = [], $multiple = false, $query = null)
    {
        $query = parent::apiQuery($options, $multiple, $query);

        $inputs = Trivet::getInputs();

        if (isset($inputs['type'])) {
            $query->onlyTypes($inputs['type']);
        }

        //   print_r(Trivet::getInputs());
        //   exit;

        return $query;
    }

    public function scopeOnlyTypes($query, $types)
    {
        $types = (array) $types;
        foreach ($types as $type) {
            switch ($type) {
                case 'types':
                    $slug = 'art-type';
                    break;

                case 'mediums':
                    $slug = 'art-medium';
                    break;

                case 'types':
                    $slug = 'art-type';
                    break;
            }
        }
        //   print_r($types);
        //   exit;
    }

    public function scopeSelf($query)
    {
        $slug = isset($this->_is) ? $this->_is : str_replace('_', '-', snake_case(class_basename(get_called_class())));
        if ($slug != 'category') {
            $query->where([
                'slug' => $slug,
            ]);
        }

        return $query;
    }

    public function scopeBindToType($query)
    {
        if (get_called_class() !== 'Nitm\Content\Models\Category') {
            if (!$this->id) {
                $slug = isset($this->_is) ? $this->_is : str_replace('_', '-', snake_case(class_basename(get_called_class())));
                $model = \DB::table($this->getTable())->where([
                    'slug' => $slug,
                ])->first();
                if ($model) {
                    $this->fill((array)$model);
                    $query->allChildren();
                }
            }
        }
    }

    public function newQuery()
    {
        $query = parent::newQuery();
        if ($this->bindToType && !$this->id) {
            $slug = isset($this->_is) ? $this->_is : str_replace('_', '-', snake_case(class_basename(get_called_class())));
            $model = \DB::table($this->getTable())->where([
                'slug' => $slug,
            ])->first();
            if ($model) {
                $this->fill((array)$model);
                $query->allChildren();
            }
        }
        return $query;
    }
}
