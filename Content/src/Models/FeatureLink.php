<?php

namespace Nitm\Content\Models;

use Model;

/**
 * Model.
 */
class FeatureLink extends Model
{
    use \October\Rain\Database\Traits\Validation, \Nitm\Content\Traits\Model;

    /*
     * Validation
     */
    public $rules = [];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_feature_links';

    public $belongsTo = [
        'feature' => [
            'Nitm\Content\Models\RelatedFeature',
            'otherKey' => 'id',
            'key' => 'feature_id'
        ],
    ];

    public $implement = [
        'Nitm.Content.Behaviors.DynamicContentRelation',
    ];

    public $visible = [
        'id', 'remote_type', 'remote_class',
        'remote_table', 'remote_id', 'feature_id'
    ];

    public $fillable = [
        'id', 'remote_type', 'remote_class',
        'remote_table', 'remote_id', 'feature_id',
        'remote_type'
    ];

    public function __construct()
    {
        // For setting the feature ID when creating a new recordfinder
        parent::__construct();
        $this->feature_id = array_get(\Backend\Classes\BackendController::$params, '0');
        if ($this->feature_id) {
            $this->remote_type = $this->feature->type->slug;
        }
    }

    public function beforeCreate()
    {
        $this->populateRemoteInfo();
    }

    public function beforeUpdate()
    {
        $this->populateRemoteInfo();
    }

    public function populateRemoteInfo($options = null)
    {
        $options = $options ?? post('FeatureLink');
        $type = ucfirst(explode('-', $this->remote_type)[0]);
        $relation = strtolower($type);
        if (class_exists($type) && $type != 'Event') {
            $class = $type;
        } else {
            $class = '\\Nitm\\Content\\Models\\' . $type;
            $class = class_exists($class) ? $class : '\\Nitm\\Content\\Models\\' . $type;
        }

        if (class_exists($class) && $class !== Event::class) {
            $this->remote_class = ltrim($class, '\\');
            $this->remote_table = (new $class())->getTable();
            $remoteId = array_get($options, $relation);
            $this->remote_id = $remoteId ?? $this->remote_id;
        } else {
            $this->remote_class = Feature::class;
            $this->remote_table = (new Feature())->getTable();
            $this->remote_id = $this->feature_id;
        }
        unset($this->attributes['featureTitle']);
    }

    public function getRemoteAttribute()
    {
        $remote = explode('-', $this->remote_type);
        if ($this->relationLoaded($remote[0])) {
            return $this->getRelation($remote[0]);
        }

        return null;
    }

    public function getRemoteTitleAttribute()
    {
        return $this->remote ? $this->remote->title : '(not set)';
    }

    public function getRemoteTypeNameAttribute()
    {
        return class_basename($this->remote_class);
    }

    // Attribute getters

    public function getFeatureTitleAttribute()
    {
        return $this->feature ? $this->feature->title() : '(not set)';
    }

    /**
     * {@inheritdoc}
     */
    public function newEloquentBuilder($query)
    {
        return new \Nitm\Content\Eloquent\Builder($query);
    }
}
