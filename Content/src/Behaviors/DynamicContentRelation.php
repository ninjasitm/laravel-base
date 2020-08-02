<?php

namespace Nitm\Content\Behaviors;

use DB;
use Nitm\Content\Models\Category;

/**
 * This behavior adds relations that could be supported by this class
 * For example a feature may include art || event || user informaiton  hwoever this may not be known during runtime. THis class adds all poential y supportedrelations
 * The implementing class canalso describe the dynamicContentConfig property to determine the keys for the relations THis can come in two flavors:
 * Global
 *    [
 *       key => column,
 *       otherKey => column
 *    ]
 * Per relation
 *    [
 *       relation => [
 *       key => column,
 *       otherKey => column
 *    ],
 *    ...
 *    ].
 */
class DynamicContentRelation extends \October\Rain\Extension\ExtensionBase
{
    public $owner;
    public $key = 'remote_id';
    public $typeKey = 'remote_type';
    public $otherKey = 'id';

    protected $_belongsTo = [];

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;
        $ownerClass = get_class($this->owner);

        $dynamicTypes = $this->getRemoteTypeOptions();
        $key = $this->key;
        $otherKey = $this->otherKey;
        $dynamicContentConfig = $this->getDynamicContentOptions();

        foreach ($dynamicTypes as $slug => $model) {
            $key = array_get($dynamicContentConfig, 'key', $key);
            $otherKey = array_get($dynamicContentConfig, 'otherKey', $otherKey);
            $conditions = array_get($dynamicContentConfig, 'conditions', null);
            $class = array_get($dynamicContentConfig, 'class', $this->getModelClass($model));

            $slug = strtolower($model);
            if (!isset($this->owner->belongsTo[$slug])) {
                if (class_exists($class)) {
                    $this->owner->belongsTo[$slug] = [
                        $class, 'key' => $key, 'otherKey' => $otherKey,
                        'order' => 'id desc',
                     ];
                    if ($conditions) {
                        if (is_callable($conditions)) {
                            $conditions = call_user_func_array($conditions, [$this->owner, $slug]);
                        }
                        $this->owner->belongsTo[$slug]['conditions'] = $conditions;
                    }
                }
            }
        }
        $this->owner->bindEvent('model.afterFetch', [$this, 'limitRelations']);
    }

    protected function getOwnerNamespace()
    {
        return (new \ReflectionClass(get_class($this->owner)))->getNamespaceName();
    }

    protected function getModelClass($modelName, $namespace = null)
    {
        $namespace = $namespace ?: $this->getOwnerNamespace();
        $slug = strtolower($modelName);
        switch ($slug) {
           case 'post':
           $class = $namespace.'\\FeedPost';
           break;

           default:
           //Need to use special relatd classes to prevent deep eager loading
           $class = $namespace.'\\Related'.$modelName;
           break;
        }
        if (class_exists($class)) {
            return $class;
        } else {
            return '\\Nitm\\Content\\Models\\'.$modelName;
        }
    }

    public function getRemoteTypeOptions()
    {
        return \Cache::remember('dynamic-content::remote-type-options', 500, function () {
            $query = DB::query()->select('title', 'slug')->from((new Category)->getTable());
            $slug = 'feature-type';
            $query->whereIn('parent_id', function ($query) use ($slug) {
                $query->select('id')->from((new Category)->getTable())->where([
                  'slug' => $slug,
               ]);
            });

            $ret_val = [];

            foreach ($query->get() as $type) {
                $ret_val[$type->slug] = $type->title;
            }

            return $ret_val;
        });
    }

    /**
     * We adjust the relations for this behavior based on the remote relation type. This way only the needed models get loaded. The models need to be eager loaded once fetched.
     *
     * @method afterFetch
     *
     * @return [type] [description]
     */
    public function limitRelations($model = null)
    {
        $model = $model ?: $this->owner;
        if (is_callable([$model, 'appendEagerLoad'])) {
            $remote = explode('-', $model->{$this->typeKey});
            $removeTypes = array_keys(array_diff_key(array_flip(array_map('strtolower', $this->getRemoteTypeOptions())), [$remote[0] => null]));
            foreach ((array) $removeTypes as $type) {
                unset($model->belongsTo[$type]);
            }
            try {
                $model->appendEagerLoad($remote[0]);
               //  $model->appendWith($remote[0]);
                $model->appendVisible($remote[0]);
            } catch (\Exception $e) {
                \Log::error($e);
                if (\App::environment() == 'dev') {
                    //   print_r($this->owner);
                  //   exit;
                    throw $e;
                }
            }
        }
    }

    protected function getDynamicContentOptions()
    {
        $dynamicContentConfig = [
            'key' => $this->key,
            'otherKey' => $this->otherKey,
         ];
      //If the class provides custom relation keys use them
      if (property_exists($this->owner, 'dynamicContentConfig')) {
          $dynamicContentConfig = array_merge($dynamicContentConfig, (array) $this->owner->dynamicContentConfig);
      } elseif (method_exists($this->owner, 'dynamicContentConfig')) {
          $dynamicContentConfig = array_merge($dynamicContentConfig, $this->owner->dynamicContentConfig());
      }
        foreach ($dynamicContentConfig as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        return $dynamicContentConfig;
    }
}
