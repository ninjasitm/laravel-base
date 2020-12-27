<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait FiltersModels
{
    /**
     * Get the filter relation definition
     *
     * @return
     * [
     *       'relation' => string,
     *       'attributes' => array,
     *       'foreignKey' => string || callable,
     *       'localKey' => string
     *  ]
     */
    public static function getRelationFilterDefinition(): array
    {
        return [];
    }

    /**
     * Get the filter definition
     *
     * @param [type] $relation
     * @param [type] $link
     * @return array [
     *  'relation' => string,
     *  'attributes' => array,
     *  'localKey' => string|callable: function ($query, $tableAlias) {},
     *  'foreignKey' => string
     * ]
     */
    public static function getFilterDefinition($relation = null, $link = null): array
    {
        return [];
    }

    /**
     * Get all filterable relations for a model
     *
     * @param [type] $class
     * @return array
     */
    public static function getFilterableRelations($class = null): array
    {
        return [];
    }

    /**
     * Is the relation filterable?
     *
     * @param string $relation
     * @param string $class
     * @return boolean
     */
    public static function isFilterableRelation(string $relation, string $class = null): bool
    {
        $filterable = static::getFilterableRelations($class);
        return in_array($relation, $filterable, true) || array_key_exists($relation, $filterable);
    }

    /**
     * Get all filterable relations implicitly
     *
     * @param [type] $class
     * @return array
     */
    public static function getFilterableRelationsImplicitly($class = null): array
    {
        $reflector = new \ReflectionClass($class ?? static::class);
        $duration = app()->environment(['dev', 'local']) ? now() : now()->addHours(6);
        return cache()->remember(
            Str::slug($reflector->name) . '-relations',
            $duration,
            function () use ($reflector) {
                $relations = [];
                foreach ($reflector->getMethods() as $reflectionMethod) {
                    $returnType = $reflectionMethod->getReturnType();
                    // \Log::info("{$reflectionMethod->getName()} | Return type: $returnType\n");
                    if ($returnType) {
                        if (in_array(class_basename($returnType->getName()), [
                            'Relation',
                            'HasOne', 'HasMany', 'BelongsTo',
                            'BelongsToMany', 'MorphToMany', 'MorphTo',
                            'HasOneThrough', 'HasManyThrough', 'MorphMany'
                        ])) {
                            $relations[$reflectionMethod->name] = true;
                        }
                    }
                }
                return $relations;
            }
        );
    }
}
