<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Nitm\Helpers\CollectionHelper;
use Illuminate\Database\Eloquent\Model;

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
    
    /**
     * Filter the given relation by the name
     * 
     * @param mixed $query
     * @param string $relationName
     * @param mixed $data
     * @param bool $exclusive
     * @return mixed
     *
     * @return void
     */
    public function filterByRelation($query, string $relationName, $data, bool $exclusive = true)
    {
        if (!CollectionHelper::isCollection($data)) {
            $data = collect(is_array($data) ? $data : [$data]);
        }
        if ($data->count()) {
            $method = $exclusive? 'whereHas' : 'orWhereHas';
            $query->$method(
                $relationName,
                function ($query) use ($data) {
                    $query->whereIn(
                        $query->getModel()->getTable() . '.id',
                        collect($data)->map(
                            function ($d) {
                                return intval($d instanceof Model ? $d->id : $d);
                            }
                        )
                    );
                }
            );
        }
    }

    /**
     * Filter by the given relation
     * 
     * @param mixed $query
     * @param string $relationName
     * @param mixed $data
     * @param array $morphable
     * @param bool $exclusive
     * @return mixed
     *
     * @return void
     */
    public function scopeFilterByRelation($query, string $relationName, $data, bool $exclusive = true)
    {
        $this->filterByRelation($query, $relationName, $data, $exclusive);
    }
    
    /**
     * Filter the given morph relation by the name
     * 
     * @param mixed $query
     * @param string $relationName
     * @param mixed $data
     * @param array $morphable
     * @param bool $exclusive
     * @return mixed
     *
     * @return void
     */
    public function filterByMorphRelation($query, string $relationName, $data, array|Collection $morphable = [], bool $exclusive = true)
    {
        if (!CollectionHelper::isCollection($data)) {
            $data = collect(is_array($data) ? $data : [$data]);
        }
        if ($data->count()) {
            $method = $exclusive ? 'whereHasMorph' : 'orWhereHasMorph';
            $query->whereHas(
                $relationName,
                function ($query) use ($data) {
                    $query->whereIn(
                        $query->getModel()->getTable() . '.id',
                        collect($data)->map(
                            function ($d) {
                                return intval($d instanceof Model ? $d->id : $d);
                            }
                        )
                    );
                }
            );
        }
    }

    /**
     * Filter by the morphable relation
     * 
     * @param mixed $query
     * @param string $relationName
     * @param mixed $data
     * @param array $morphable
     * @param bool $exclusive
     * @return mixed
     *
     * @return void
     */
    public function scopeFilterByMorphRelation($query, string $relationName, $data, array|Collection $morphable = [], bool $exclusive = true)
    {
        $this->filterByMorphRelation($query, $relationName, $data, $morphable, $exclusive);
    }

    /**
     * Filter By Created
     *
     * @param  mixed $query
     * @param  mixed $value
     * @return void
     */
    public function scopeFilterByCreatedAt($query, $value)
    {
        $this->filterByDate($query, $value);
    }

    /**
     * Filter By Updated
     *
     * @param  mixed $query
     * @param  mixed $value
     * @return void
     */
    public function scopeFilterByUpdatedAt($query, $value)
    {
        $this->filterByDate($query, $value, 'updated_at');
    }

    /**
     * Filter By Date
     *
     * @param  mixed $query
     * @param  mixed $field
     * @param  mixed $value
     * @return void
     */
    public function filterByDate($query, $value, $field = 'created_at')
    {
        if (is_array($value) && !empty(array_filter($value, function ($value) {
            $isStringDate = (is_string($value) && DateTime::createFromFormat('Y-m-d H:i:s', $value) !== false);
            $isTimestamp = ((string) (int) $value === (string) $value) && ($value <= PHP_INT_MAX) && ($value >= ~PHP_INT_MAX);
            return $isStringDate || $isTimestamp;
        }))) {
            $start = Carbon::parse(array_shift($value));
            $end = empty($value) ? Carbon::now() : Carbon::parse(array_pop($value));
            $query->whereBetween($field, [$start, $end]);
        } else if (is_string($value)) {
            $query->where($field, Carbon::parse((string)$value));
        }
    }
}
