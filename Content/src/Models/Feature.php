<?php

namespace Nitm\Content\Models;

use Model;

/**
 * Model.
 */
class Feature extends BaseContent
{
    public $implement = [
        'Nitm.Content.Behaviors.Blamable',
        'Nitm.Content.Behaviors.Search',
        'Nitm.Content.Behaviors.Permissions',
        'Nitm.Content.Behaviors.Activity',
    ];
    /*
     * Validation
     */
    public $rules = [
        'title' => 'string|required',
        'type_id' => 'integer|required|exists:nitm_categories,id',
    ];

    protected $slugs = [
        'slug' => 'title',
    ];

    public $visible = [
        'id', 'title', 'description', 'image', 'type', 'is_active', 'feature',
    ];

    public $hidden = ['link'];

    public $with = [
        'image', 'type',
    ];

    public $fillable = [
        'id', 'title', 'description', 'image', 'type', 'is_active', 'feature', 'type_id', 'slug'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;

    /**
     * @var string The database table used by the model
     */
    public $table = 'nitm_features';

    public $hasOne = [
        'link' => ['Nitm\Content\Models\FeatureLink', 'key' => 'feature_id', 'otherKey' => 'id'],
        'type' => ['Nitm\Content\Models\FeatureType', 'otherKey' => 'type_id', 'key' => 'id'],
    ];

    public function toArray($forApi = false)
    {
        $ret_val = parent::toArray($forApi);
        if (array_get($ret_val, 'link') && $this->link->remote) {
            $ret_val['feature'] = $this->link->remote->toArray(true);
            $ret_val['id'] = $ret_val['feature']['id'];
            if (!array_get($ret_val, 'image.href')) {
                if ($this->link->remote instanceof User) {
                    $image = array_get($ret_val, 'feature.avatar');
                } else {
                    $image = array_get($ret_val, 'feature.image');
                }
                if ($image) {
                    $ret_val['feature']['image'] = [
                        'id' => md5(array_get($image, 'url')),
                        'type' => 'link',
                        'url' => array_get($image, 'url'),
                        'href' => array_get($image, 'url'),
                    ];
                }
            }
        } else {
            $ret_val['feature'] = $ret_val;
        }
        unset($ret_val['link']);
        $ret_val['type'] = $this->contentType;

        return $ret_val;
    }

    protected function allowedFeatureFields()
    {
        return ['id', 'slug', 'title', 'image', 'date', 'author'];
    }

    /**
     * Get the supported event types.
     *
     * @param bool $getQuery Should we requrn the query?
     *
     * @return \Illuminate\Eloquent\(Collection|Builder) result
     */
    public function supportedTypes($getQuery = false)
    {
        return $this->getWithChildren(FeatureType::class, $getQuery);
    }

    public function beforeUpdate()
    {
        $this->updateLink();
    }

    protected function updateLink()
    {
        /*
         * If the type of thefeature was changed then remove the feature link.
         **/
        if ($this->getOriginal('type_id') != $this->type_id) {
            if ($this->link) {
                $this->link->delete();
            }
        }
    }

    /**
     * Get the event type options.
     *
     * @return array The type ID options
     */
    public function getTypeIdOptions()
    {
        return $this->getNestedDropdownOptions('supportedTypes');
    }

    public function getContentTypeAttribute()
    {
        if ($this->type) {
            $type = explode('-', $this->type->slug);

            return $type[0];
        }
        return '';
    }

    public function isType($type)
    {
        return $this->type->slug == $type . '-feature-type';
    }

    //Attribute getters

    public function getRemoteTitleAttribute()
    {
        if ($this->remote) {
            try {
                return $this->remote->title;
            } catch (\Excption $e) {
                return '(error: ' . $e->getMessage() . ' )';
            }
        } else {
            return '(not set)';
        }
    }
    public function getRemoteAttribute()
    {
        if ($this->link) {
            try {
                return $this->link->{$this->remoteTypeName};
            } catch (\Excption $e) {
                return '(error: ' . $e->getMessage() . ' )';
            }
        } else {
            return $this->type;
        }
    }

    public function getRemoteTypeNameAttribute()
    {
        return $this->contentType;
    }
}
