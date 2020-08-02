<?php

namespace Nitm\Api\Traits;

use DB;
use Nitm\Api\Models\Mapping;
use Nitm\Api\Models\Logs;
use Nitm\Api\Classes\Trivet;
use Nitm\Api\Helpers\Cache;

trait ApiTrait
{
    /**
     * Helper function to save model.
     *
     * @method saveModel
     *
     * @param [type] $model      [description]
     * @param [type] $attributes [description]
     *
     * @return {[type] [description]
     */
    public function saveModel($model)
    {
        $data = Trivet::filterInput($this->columns_to_operate);
        $fillable = $model->fillable && count($model->fillable) ? $model->fillable : array_keys($data);
        $data = array_intersect_key($data, array_flip($fillable));
        foreach ($data as $field => $value) {
            try {
                if (in_array($field, $fillable) || $model->hasSetMutator($field)) {
                    if ($model->hasSetMutator($field)) {
                        call_user_func([$model, 'set'.camel_case($field).'Attribute'], $value);
                    } else {
                        $model->$field = $value;
                    }
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
        if ($model->save(null, $model->getSessionKey())) {
            $this->data = $model->fresh();
            Cache::set(Cache::getKey(static::getCacheKeyInputs('read')), $this->data, null, false, config('cache.limits.'.implode('-', \Route::current()->parameters())));
        } else {
            $this->data = $model->errors();
        }
    }

    public static function getCacheKeyInputs($type='read', $data=null)
    {
        $ret_val = $data ?: Trivet::getInputs();
        switch ($type) {
         case 'update':
         case 'delete':
         case 'create':
         $these = ['req', 'id', 'do', 'key'];
         break;

         default:
         $these = ['req', 'id', 'do', 'key'];
         break;
      }
        return array_only($ret_val, $these);
    }

    public function shouldRefresh($mapping)
    {
        //Let's disable this for now since we're gonna use caching
      return false;
        $class = $mapping->relatedtable;
        $lastAccess = Logs::selectRaw('MAX(updated_at) as updated_at')->where([
           'fullurl' => Trivet::fullUrl(),
        ])->first();
        if (class_exists($class)) {
            try {
                return (new $class())->hasNew($lastAccess->updated_at);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Prepare the query for use in the read operation.
     *
     * @param Mapping $mapping   The API mapping to use with the query
     * @param string  $namespace The namespace for the class we can use
     * @param bool    $multiple  If this a multiple query?
     *
     * @return Builder The prepared query
     */
    public static function getQuery(Mapping $mapping, $namespace, $multiple = false)
    {
        if (class_exists($class = $mapping->relatedtable) || class_exists(($class = Trivet::getModelClass($mapping->reqparameter, $namespace)))) {
            $query = $class::apiQuery(Trivet::getInputs(), $multiple);
            \Nitm\Content\Models\BaseContent::setForApi(true);
            $table = $query->getModel()->getTable();
        } else {
            $table = $mapping->relatedtable;
            $query = DB::table($mapping->relatedtable)->addSelect($mapping->respondeFields);
        }

        $orderByIndexKey = Trivet::getPrimaryKey($mapping->relatedtable);
        if ($orderByIndexKey) {
            $orderBy = $mapping->order_by ?: 'asc';
            $query->orderBy($orderByIndexKey, $orderBy);
        }

        static::applyQueryOptions($query, Trivet::getInputs(), $table);

        return $query;
    }

    /**
     * Apply the specified query options.
     *
     * @param Builder $query   The query to apply the options to
     * @param array   $options Extra query options
     * @param string  $table   The table this query should be run on
     */
    protected static function applyQueryOptions($query, $options, $table)
    {
        $queryOptions = static::getQueryOptionsFromUser($table, $options);
        foreach ($queryOptions as $method => $params) {
            if (method_exists($query, $method)) {
                call_user_func_array([$query, $method], $params);
            }
        }
    }

    /**
     * Support some user specific query parameters.
     *
     * @param string     $table       The table to use for finding primary key and schema
     * @param null|array $userOptions The user specified options
     *
     * @return array The query options that can be applied tothe query
     */
    protected static function getQueryOptionsFromUser($table, $userOptions = null)
    {
        $allInputs = $userOptions ?: Trivet::getInputs();
        $result = [];
        foreach ($allInputs as $op => $value) {
            switch ((string) $op) {
               case 'sortBy':
               $result['orderBy'] = [$value, array_pull($allInputs, 'sort', 'desc')];
               break;

               case 'sort':
               if (!isset($allInputs['sortBy'])) {
                   $result['orderBy'] = [Trivet::getPrimaryKey($table), $value];
               }
               break;

               case 'limit':
               $result['take'] = (int) $value;
               break;

               case 'offset':
               $result['skip'] = (int) $value;
               break;
            }
        }

        return $result;
    }

    protected static function getRelatedModelClass($modelClass)
    {
        $parts = explode('\\', $modelClass);
        $parts[count($parts) - 1] = 'Related'.$parts[count($parts) - 1];
        $relatedModelClass = implode('\\', $parts);
        return $relatedModelClass;
    }
}
