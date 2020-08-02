<?php

namespace Nitm\Content\Models;

use Model;
use Nitm\Content\Observers\BaseObserver;

/**
 * Activity model for handling activity streams. Based on Activity Streams 1.0/2.0 proposed design
 * Actvity Streams 1.0: http://activitystrea.ms/specs/json/1.0/
 * Activity Streams 2.0: http://www.w3.org/TR/activitystreams-core/.
 * Notably the use of a 'target' propoperty is supported but not implemented by default.
 */
class Activity extends Model
{
    use \October\Rain\Database\Traits\Validation, \Nitm\Content\Traits\Model;

    /*
     * Validation
     */
    public $rules = [
        'object' => 'required',
        'actor' => 'required',
    ];

    public $with = [
        'post.featured_images',
        'user.avatar',
        'art.image', 'art.type',
        'event.image', 'event.category', 'event.type', 'event.locations',
    ];

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_activity';

    //  public $jsonable = ['object', 'actor', 'target'];

    public $casts = [
        'object' => 'array',
        'actor' => 'array',
        'target' => 'array',
    ];

    public $fillable = ['object', 'actor', 'target', 'title', 'verb', 'remote_type', 'remote_id'];

    public $visible = [
        'object', 'actor', 'target', 'title', 'verb', 'created_at', 'data',
    ];

    public $hasOne = [
        'watched' => [
            'Nitm\Content\Models\ContentView',
            'key' => 'id',
            'otherKey' => 'content_id',
            'scope' => 'watchedContent',
        ],
        'watchedCount' => [
            'Nitm\Content\Models\ContentView',
            'key' => 'id',
            'otherKey' => 'content_id',
            'scope' => 'watchedContent',
            'count' => true,
        ],
    ];

    public $implement = [
        '@Nitm.Content.Behaviors.Search',
        '@Nitm.Content.Behaviors.Permissions',
        '@Nitm.Content.Behaviors.OriginalDynamicContentRelation',
    ];

    public static function recordActivity(BaseObserver $observer)
    {
        $remote = is_object($observer->getModel()) ? $observer->formatObject($observer->getModel()) : $observer->getModel();
        static::create([
            'verb' => $observer->getActionName(),
            'actor' => $observer->getActor(),
            'object' => $observer->getObject(),
            'title' => $observer->getTitleString(),
            'target' => $observer->getTarget(),
            'is_admin_action' => $observer->getIsAdminAction(),
            'remote_type' => $remote['type'],
            'remote_class' => @$remote['_model']['class'],
            'remote_id' => @$remote['_model']['id'],
        ]);
        $observer->finish();
    }

    /**
     * This is the only known way to populate the current associated model information.
     *
     * @return array [description]
     */
    public function getDataAttribute($attribute = 'object')
    {
        $attribute = $attribute ?: 'object';
        $data = $this->{$attribute};
        $modelClass = array_get($data, '_model.class', null);
        if (class_exists($modelClass) && strpos($modelClass, 'Related') === false) {
            $parts = explode('\\', $modelClass);
            $parts[count($parts) - 1] = 'Related' . $parts[count($parts) - 1];
            $relatedModelClass = implode('\\', $parts);
            if (class_exists($relatedModelClass)) {
                $modelClass = $relatedModelClass;
            }
        }
        $dataId = explode('/', $data['id']);
        $id = array_get($data, '_model.id', array_pop($dataId));
        $type = $data['type'];

        $ret_val = $this->{$type} ? $this->{$type}->toArray() : [];
        //   if (empty($ret_val)) {
        //       if ($modelClass && class_exists($modelClass)) {
        //           $model = $modelClass::apiFind($id, ['for' => 'feed']);
        //           if ($model) {
        //               $ret_val = $model->toArray();
        //           } else {
        //               $ret_val = [];
        //           }
        //       }
        //   }

        return $ret_val;
    }

    public static function getTypeOptions()
    {
        return static::query()->distinct()
            ->select('remote_type')
            ->orderBy('remote_type', 'asc')
            ->get()->lists('remote_type', 'remote_type');
    }

    public static function getActionOptions()
    {
        return static::query()->distinct()
            ->select('verb')
            ->orderBy('verb', 'asc')
            ->get()->lists('verb', 'verb');
    }

    public function getWatched()
    {
        return (new ContentView())->newQuery()->where([
            'content_id' => $this->id,
            'content_type' => $this->is,
        ])->get();
    }

    public function getWatchedCount()
    {
        return (new ContentView())->newQuery()->where([
            'content_id' => $this->id,
            'content_type' => $this->is,
        ])->count();
    }

    public function getObjectIdAttribute()
    {
        return $this->object['id'];
    }

    public function getObjectTypeAttribute()
    {
        return $this->object['type'];
    }

    public function getTargetIdAttribute()
    {
        return $this->target['id'];
    }

    public function getTargetTypeAttribute()
    {
        return $this->target['type'];
    }

    public function getActorIdAttribute()
    {
        return $this->actor['id'];
    }

    public function getActorNameAttribute()
    {
        return array_get($this->actor, 'displayName', array_get($this->actor, 'name'));
    }

    public function getActorDisplayNameAttribute()
    {
        return $this->actor['displayName'];
    }

    public function getActorTypeAttribute()
    {
        return $this->actor['type'];
    }

    /**
     * Scopes.
     */
    public function scopeWatchedContent($querry)
    {
        $query->where(['content_type' => $this->objectType]);
    }

    public function scopeNew($query)
    {
        $query->orderBy('created_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        $query->filterByAction('created');
        $query->whereRaw("object::json->>'type' = 'feature'");
    }

    public function scopeFilterByNew($query, $action)
    {
        $query->new();
    }

    public function scopeFilterByFeatured($query, $action)
    {
        $query->featured();
    }

    public function scopeFilterByAction($query, $action)
    {
        $query->whereIn('verb', (array) $action);
    }

    public function scopeFilterByType($query, $types)
    {
        $query->where(function ($query) use ($types) {
            $parts = [];
            foreach ($types as $type) {
                //  $query->orWhereRaw("object::jsonb @> '{\"type\":\"$type\"}'::jsonb");
                //  $query->orWhereRaw("target::jsonb @> '{\"type\":\"$type\"}'::jsonb");
                $query->orWhere(['remote_type' => $type]);
            }
        });
    }

    public function scopeFilterByActor($query, $actors)
    {
        $query->where(function ($query) use ($actors) {
            $parts = [];
            foreach ($actors as $actor) {
                $query->orWhereRaw("actor::jsonb @> '{\"name\":\"$actor\"}'::jsonb");
                $query->orWhereRaw("actor::jsonb @> '{\"username\":\"$actor\"}'::jsonb");
            }
        });
    }

    public function toArray()
    {
        $attributes = array_reverse(parent::toArray());
        //Disabling this as it causes timeouts. Need to figure out better way to store this info.
        //   $attributes['watchedCount'] = $this->getWatchedCount();
        $target = !empty($attributes['target']) ? $attributes['target'] : $attributes['object'];
        $attributes['type'] = 'feed';

        unset($attributes['actor']['_model'], $attributes['target']['_model'], $attributes['object']['_model']);
        $data = array_pull($attributes, 'data', $this->getDataAttribute());
        if (!empty($data)) {
            $attributes['object']['image'] = $this->getImage('object');
            // $attributes['actor']['image'] = $this->getImage('actor');
            $attributes['object'] = array_merge($attributes['object'], array_diff_key($data, [
                'type' => null,
            ]));
            $attributes['object']['id'] = $this->getFixedPublicId($attributes['object']['id'], $attributes['object']['type']);
            $attributes['id'] = array_get($data, 'id', $attributes['object']['id']);
            $type = $this->objectType;
            $attributes = array_filter($attributes);
            if (isset($attributes[$type])) {
                unset($attributes[$type]);
            }

            // $this->object = $attributes['object'];
            // $this->target = $attributes['target'];
            // $this->save();

            return array_reverse($attributes);
        }

        return [];
    }

    public function getFixedPublicId($id, $type = null)
    {
        $parts = explode('/', $id);
        if ($type == 'user') {
            return array_pop($parts);
        }
        if (count($parts) == 2) {
            return implode('-', [
                str_slug($parts[0]),
                md5($parts[1]),
            ]);
        }

        return $id;
    }

    /**
     * We need to find the correct image if it's not populated.
     *
     * @method getImage
     *
     * @param [type] $type [description]
     *
     * @return [type] [description]
     */
    protected function getImage($type)
    {
        $result = [];
        $item = $this->{$type};
        if (!$item) {
            return [];
        }
        if ($type == 'actor') {
            $result = array_get($item, 'avatar', array_get($item, 'image', []));
        } else {
            $result = array_get($item, 'image');
        }

        if (!array_get($result, 'url')) {
            $data = $this->getDataAttribute($type);
            if ($data) {
                if ($type == 'actor') {
                    $result = array_get($data, 'avatar', []);
                } else {
                    $result = array_get($data, 'image', []);
                }
            }
        }

        /*
         * Bugfix for using href instead of url.
         */
        if (isset($result['href']) && !isset($result['url'])) {
            $result['url'] = $result['href'];
        }
        $result['id'] = md5($item['id']);

        return $result;
    }
}
