<?php

namespace Nitm\Api\Helpers;

use Cache as RealCache;
use Carbon\Carbon;

/**
 * Cache helper class for Octopus Artworks API.
 */
class Cache
{
    protected static function flatten($array)
    {
        $ret_val = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $ret_val = array_merge($ret_val, static::flatten($v));
            } else {
                $ret_val[] = $k.':'.$v;
            }
        }

        return $ret_val;
    }
    public static function getKey(array $options)
    {
        //Do not add the api token to the cache key
        $options = array_except($options, ['api_token']);
        $options = static::flatten($options);

        return implode('::', array_map('strtolower', $options));
    }
    /**
     * Get a models cached at the specified key location.
     *
     * @param string $key [description]
     *
     * @return mixed [description]
     */
    public static function getAll($key)
    {
        $data = RealCache::get($key);
        $class = array_pull($data, '__cacheClass');
        if (!is_null($class) && class_exists($class)) {
            $data = \October\Rain\Database\Collection::make(array_map(function ($attributes) use ($class) {
                try {
                    return static::instantiate($class, $attriubtes);
                } catch (\Exception $e) {
                    return $attributes;
                }
            }, $data['__cacheData']));
        } else {
            $data = array_get($data, '__cacheData', $data);
        }

        return $data;
    }

    /**
     * Get a model cached at the specified key location.
     *
     * @param string $key [description]
     *
     * @return mixed [description]
     */
    public static function get($key, $shouldInstantiate = false)
    {
        $data = RealCache::get($key);
        $class = array_pull($data, '__cacheClass');
        if (!is_null($class) && class_exists($class) && $shouldInstantiate) {
            try {
                /*
                  There's a bug here that results in an incomplete model. Not a bug but a limitation. Removing instantion request
                */
                $data = static::instantiate($class, $data['__cacheData']);
               //  $data = array_get($data, '__cacheData', $data);
            } catch (\Exception $e) {
                $data = array_get($data, '__cacheData', $data);
            }
        } else {
            $data = array_get($data, '__cacheData', $data);
        }

        return $data;
    }

    /**
     * Set a new model to the cache.
     *
     * @param string      $key   [description]
     * @param mixed       $model [description]
     * @param string|null $class [description]
     * @param bool        $many  [description]
     */
    public static function set($key, $model, $class = null, $many = false, $duration = null)
    {
        if (is_object($model)) {
            if ($model instanceof \lluminate\Database\Eloquent\Collection) {
                $class = $class ?: get_class($model->first());
            } elseif ($model instanceof \Model) {
                $class = $class ?: get_class($model);
            }
            $model = [
                '__cacheClass' => $class,
                '__cacheData' => $many ? static::modelsToArray($model) : static::toArray($model),
            ];
        } elseif (is_array($model) && isset($model['__cacheData'])) {
            $model['__cacheData'] = $many ? static::modelsToArray($model['__cacheData']) : static::toArray($model['__cacheData']);
        } else {
            $model['__cacheData'] = $many ? static::modelsToArray($model) : static::toArray($model);
        }
        $duration = $duration ?: \Config::get('api.apiCacheDuration');

        $now = Carbon::now();
        if (floatval($duration) < 1 && floatval($duration) > 0) {
            $now->addSeconds($duration*1000);
        } else {
            $now->addMinutes($duration);
        }
        RealCache::add($key, $model, $duration);
    }

    /**
     * Utility functin to automatically store and retrieve cached data.
     *
     * @param array    $keyParts
     * @param array    $data     [description]
     * @param int      $duration [description]
     * @param callable $callback [description]
     * @param bool     $many     [description]
     * @param bool     $refresh  Should this request force a refresh?
     *
     * @return mixed [description]
     */
    public static function remember($keyParts, $data, $callback, $duration = null, $many = false, $refresh = false, $shouldInstantiate = false)
    {
        if (isset($keyParts['api_token'])) {
            $keyParts['api_token'] = md5($keyParts['api_token']);
        }
        $key = static::getKey((array) $keyParts);
        if (RealCache::has($key) && !$refresh) {
            if ($many) {
                $data = static::getAll($key, $shouldInstantiate);
            } else {
                $data = static::get($key, $shouldInstantiate);
            }

            return $data;
        } else {
            $result = call_user_func_array($callback, [$data]);
            static::set($key, $result, null, $many, $duration);

            return array_get($result, '__cacheData', $result);
        }
    }

    /**
     * Convert an array of objects to an array of arrays.
     *
     * @param array | Collection $objects [description]
     *
     * @return array Converted obects
     */
    public static function modelsToArray($objects)
    {
        $ret_val = [];
        foreach ($objects as $idx => $object) {
            $ret_val[$idx] = static::toArray($object);
        }

        return $ret_val;
    }

    /**
     * Convert an object to an array.
     *
     * @param mixed|Model $object [description]
     *
     * @return array The array representation of the object
     */
    public static function toArray($object)
    {
        if (!is_object($object)) {
            return $object;
        } elseif ($object instanceof \Model) {
            return $object->toArray();
        } else {
            return $object;
        }
    }

    /**
     * Create an objecct from an array of attributes.
     *
     * @param string $class      [description]
     * @param array  $attributes [description]
     *
     * @return $class Object
     */
    protected static function instantiate($class, $attributes)
    {
        $model = new $class();
        foreach ($attributes as $attribute => $value) {
            //Simple way of determining if the current valueis a relation
            try {
                $relation = $model->{$attribute}();
                if (is_object($relation) && $relation instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $model->setRelation($attribute, $value);
                } else {
                    $model->{$attribute} = $value;
                }
            } catch (\Exception $e) {
                $model->{$attribute} = $value;
            }
        }

        return $model;
    }

    /**
     * Convert an array of arrays to an array of objects.
     *
     * @param array $objects [description]
     *
     * @return array Converted objects
     */
    protected static function instantiateModels($class, $objects)
    {
        $ret_val = [];
        foreach ($objects as $object) {
            $ret_val[] = static::instantiate($class, $object);
        }

        return $ret_val;
    }
}
