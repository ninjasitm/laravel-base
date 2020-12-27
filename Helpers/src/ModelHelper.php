<?php

namespace Nitm\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * This class provides configuration helper functions for config variables.
 *
 * @author malcolm@ninjasitm.com
 */
class ModelHelper
{
    /**
     * Get the tables based on the database driver.
     *
     * @param string $key The value to get from the config
     * @param string $db  The name of the database
     *
     * @return [type] [description]
     */
    public static function getIs($model)
    {
        $parts = explode('\\', get_class($model));
        $str = array_pop($parts);
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $str, $matches);

        return strtolower(implode($matches[0], '-'));
    }

    /**
     * Determine the boolean value of a variable
     *
     * @param [type] $value
     * @param boolean $returnNull
     * @return void
     */
    public static function boolval($value, $returnNull = false)
    {
        $boolval = (is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $value);
        return ($boolval === null && !$returnNull ? false : $boolval);
    }

    /**
     * Setup filter relations for the specified relation
     *
     * @param string $class
     * @param string $relation
     * @param array $attributes
     * @param string|Callable $localKey
     * @param string $foreignKey
     *
     * @return void
     */
    public static function bootRelation(string $class, string $relation, array $attributes, $localKey, $foreignKey = 'id')
    {
        $relationFilter = "scopeFilterBy" . Str::camel(Str::singular($relation));
        $relationSort = 'scopeSortBy' . Str::camel(Str::singular($relation));
        $class::macro($relationFilter, function ($query, $value, $property = 'id') use ($relation) {
            $query->whereHas($relation, function ($query) use ($property, $value) {
                $table = $query->getModel()->getTable();
                if (is_array($value)) {
                    $ids = array_map(function ($v) use ($property) {
                        if (is_object($v) && property_exists($v, $property)) {
                            return $v->$property;
                        } elseif (is_array($v)) {
                            return Arr::get($v, $property);
                        } else {
                            return $v;
                        }
                    }, $value);
                    $query->whereIn($table . '.' . $property, array_filter($ids));
                } elseif (is_object($value) && property_exists($value, $property)) {
                    if (is_string($value) && !is_numeric($value)) {
                        $query->where($table . '.' . $property, 'like', "%{$value->$property}");
                    } else {
                        $query->where($table . '.' . $property, $value->$property);
                    }
                } elseif (is_array($value)) {
                    $query->where($table . '.' . $property, Arr::get($value, $property));
                } else if (is_string($value) && !is_numeric($value)) {
                    return $query->where($table . '.' . $property, 'like', "%{$value}%");
                } else {
                    return $query->where($table . '.' . $property, $value);
                }
            });
        });

        $class::macro($relationSort, function ($query, $attribute = 'id', $direction = 'desc') use ($relation, $localKey, $foreignKey) {
            $relationQuery = $this->$relation();
            $table = $relationQuery->getModel()->getTable();
            if (is_callable($localKey)) {
                $query->leftJoin($table, $localKey);
            } else {
                $query->leftJoin($table, $table . '.' . $foreignKey, '=', $this->getTable() . '.' . $localKey)
                    ->orderBy("$table.$attribute", $direction);
            }
        });

        if (is_array($attributes) && count($attributes)) {
            foreach ($attributes as $attribute) {
                $relationAttribute = Str::studly($relation . ' ' . $attribute);

                $class::macro('scopeFilterBy' . $relationAttribute, function ($query, $value, $property = 'id') use ($relationFilter) {
                    $this->$relationFilter($query, $value, $property);
                });

                $class::macro('scopeSortBy' . $relationAttribute, function ($query, $attribute = 'id', $direction = 'desc') use ($relationSort, $localKey, $foreignKey) {
                    $this->$relationSort($query, $attribute, $direction, $localKey, $foreignKey);
                });
            }
        }
    }

    /**
     * Determine if the give clas uses the specficied trait
     * @link https://www.php.net/manual/en/function.class-uses.php
     * @param [type] $trait
     * @param [type] $class
     * @param boolean $autoload
     *
     * @return void
     */
    public static function usesTrait($trait, $class, $autoload = true)
    {
        $traits = [];

        // Get all the traits of $class and its parent classes
        do {
            $className = is_object($class) ? get_class($class) : $class;
            if (class_exists($className, $autoload)) {
                $traits = array_merge(class_uses($class, $autoload), $traits);
            }
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        $traits = array_unique($traits);
        return isset($traits[$trait]);
    }
}