<?php

namespace Nitm\Content\Behaviors;

use Str;

/**
 * Search behavior adds search capability to model
 */
class Search extends \October\Rain\Extension\ExtensionBase
{
    public $owner;

    protected $_conditions = [];
    protected $_filters;
    protected $_searchQuery;
    protected $_columns;

    /**
     * The search conditions
     * @param array
     */
    protected $searchConditions = [];

    /**
     * Enable or disable pagination on the results
     *
     * @var bool
     */
    protected $searcnEnablePagination = true;

    /**
     * Enable or disable relation filtering
     *
     * @var bool
     */
    protected $searchEnableRelationFiltering = true;

    /**
     * Enable or disable the use of the OR clauses instead of AND for inner comparisons
     *
     * @var bool
     */
    protected $searchEnableInclusivity = true;

    /**
     * Enable or disable the use of the OR clauses instead of AND for the outer comparisons
     *
     * @var bool
     */
    protected $searchEnableDirectInclusivity = false;

    /**
     * All conditions will use where conditions by default
     *
     * @var bool
     */
    protected $searchEnableStrictExclusivity = false;

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;
        $this->_columns = $owner->getTableColumns();;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        } else {
            return $this->_searchQuery->$method(...$parameters);
        }
    }

    /**
     * Performa a search using a string or an array of parameters.
     *
     * @method scopeSearch
     *
     * @param Builder $query     [description]
     * @param mixed   $params    Supports the following Formats
     * string OR
     * [
     *   s => string,
     *   filter => [
     *     ...
     *   ]
     * ]
     *
     * @return void [description]
     */
    public function scopeSearch($query, $params = [])
    {
        $this->searchConditions = [];
        $method = $this->getClause($this->searchEnableDirectInclusivity);
        $query->$method(
            function ($query) use ($params) {
                $filter = is_array($params) ? $this->extractFilter($params) : null;
                $filter = is_array($filter) ? array_get($filter, 'filter', $filter) : null;
                $s = is_string($params) ? $params : (is_array($params) ? array_get($params, 's') : null);
                $columns = $query->getModel()->getTableColumns();
                // If we already have a search string specified then remove it from the filter
                if ($s && !empty($filter)) {
                    unset($filter['s']);
                }

                if (!empty($filter)) {
                    $query->exclusive()->directExclusivity()->filter($filter);
                }
                // $query->addSearchConditions($columns, $params);
                if (is_string($s)) {
                    $query->orWhere(
                        function ($query) use ($s, $columns) {
                            $query->addStringSearchCondition($columns, $s);
                        }
                    );
                }

                if (count($this->searchConditions)) {
                    if (!empty($filter)) {
                        $query->exclusive()->cannotFilterRelations();
                    }
                    $query->applySearchConditions();
                }
            }
        );

        if (is_array($params)) {
            $query->filter((array) array_only($params, ['order', 'sort', 'page', 'perPage']));
        }

        if (empty($query->getQuery()->orders)) {
            $key = isset($columns['updated_at']) ? ['updated_at'] : (array)$query->getModel()->primaryKey;
            foreach ($key as $k) {
                if ($k = $this->ensureSearchOrderBy($query, $k)) {
                    $query->orderBy($query->getModel()->getTable() . '.' . $k, 'desc');
                }
            }
        }
    }

    /**
     * Extract the filter information
     *
     * @param array $params the Array of parameters
     *
     * @return array
     */
    protected function extractFilter($params = [])
    {
        $params = array_get($params, 'filter', []);
        $params = is_string($params) ? json_decode($params, true) : $params;
        return is_array($params) ? $params : null;
    }

    /**
     * Add a global string search by mathcing the value to string columns.
     *
     * @method addStringSearchCondition
     *
     * @param Builder  $query     [description]
     * @param Column[] $columns   [description]
     * @param string   $value     [description]
     */
    public function scopeAddStringSearchCondition($query, $columns, $value)
    {
        $method = $this->getClause($this->searchEnableDirectInclusivity);

        $query->$method(function ($query) use ($columns, $value) {
            foreach ($columns as $name => $column) {
                //We will only match string columns to the search string
                if (in_array($column->getType()->getName(), ['string', 'text'])) {
                    $query->addSearchCondition($name, $value);
                }
            }

            if ($this->searchEnableRelationFiltering && !empty($value)) {
                $class = get_class($query->getModel());
                if (method_exists($query->getModel(), 'getFilterableRelations')) {
                    $relations = $class::getFilterableRelations($class);
                    if (is_array($relations) && !empty($relations)) {
                        $query->orWhere(function ($query) use ($relations, $value) {
                            foreach ($relations as $relation => $fields) {
                                $query->searchFilterRelation(is_numeric($relation) ? $fields : $relation, $value, true, true);
                            }
                        });
                    }
                }
            }
        });
    }

    /**
     * Add multiple conditions.
     *
     * @method StringConditions
     *
     * @param Builder  $query     [description]
     * @param Column[] $columns   [description]
     * @param array    $params    [description]
     * @return void
     */
    public function scopeAddSearchConditions($query, array $columns, array $params)
    {
        $method = $this->getClause($this->searchEnableDirectInclusivity);

        $query->$method(function ($query) use ($columns, $params) {
            foreach ($params as $field => $value) {
                if (array_key_exists($field, $columns)) {
                    $type = $columns[$field]->getType()->getName();
                    //Only add the condition of the datatype matches the column type
                    if (gettype($value) == $type || is_array($value)) {
                        $query->addSearchCondition($field, $value);
                    }
                }
            }
        });
    }

    /**
     * Add a single search condition
     *
     * @method addSearchCondition
     *
     * @param Builder $query     [description]
     * @param string  $column    [description]
     * @param mixed   $value     [description]
     */
    public function scopeAddSearchCondition($query, $column, $value)
    {
        $method = $this->getClause($this->searchEnableInclusivity, is_array($value));
        if ($query->getModel()->hasColumn($column)) {
            $definition = $query->getModel()->getTableColumns()[$column];
            $column = $query->getModel()->qualifyColumn($column);
            if (!is_array($value) && !($value instanceof \Illuminate\Support\Collection) && !($value instanceof \Illuminate\Database\Eloquent\Collection)) {
                $this->searchConditions[$method][] = [$column, static::convertSearchValue($value, $definition->getType()->getName())];
            } elseif (gettype($value) == $definition->getType()->getName() || is_array($value)) {
                $this->searchConditions[$method][] = [$column, static::convertSearchValue($value, $definition->getType()->getName())];
            }
        }
    }

    /**
     * Convert a search value to the corresponding value
     *
     * @return mixed
     */
    protected static function convertSearchValue($value, $type)
    {
        if (is_array($value)) {
            $value = array_map(function ($v) use ($type) {
                return static::convertSearchValue($v, $type);
            }, $value, array_fill(0, count($value), $type));
        } elseif (($value instanceof \Illuminate\Support\Collection) || ($value instanceof \Illuminate\Database\Eloquent\Collection)) {
            $value->transform(function ($v) use ($type) {
                return static::convertSearchValue($v, $type);
            });
        } else {
            switch ($type) {
                case "boolean":
                    $value = ModelHelper::boolval($value);
                    break;
                case "integer":
                case 'smallint':
                case 'bigint':
                    $value = intval($value);
                    break;

                case "double":
                case 'float':
                case 'decimal':
                    $value = floatval($value);
                    break;

                case "string":
                case 'text':
                case 'datetime':
                case 'date':
                case 'time':
                    $value = strval($value);
                    break;
            }
        }
        return $value;
    }

    /**
     * Apply search conditions.
     *
     * @method addSearchCondition
     *
     * @param Builder $query     [description]
     * @return void
     */
    public function scopeApplySearchConditions($query)
    {
        $method = $this->getClause(!$this->searchEnableStrictExclusivity && $this->searchEnableInclusivity || $this->searchEnableRelationFiltering);
        $query->$method(function ($query) {
            foreach ($this->searchConditions as $method => $group) {
                foreach ($group as $params) {
                    list($column, $value) = $params;
                    if (is_numeric($value)) {
                        $query->$method($column, $value);
                    } elseif (is_array($value)) {
                        $query->$method($column, $value);
                    } elseif (is_string($value) && strlen($value)) {
                        switch ($query->getConnection()->getDriverName()) {
                            case 'pgsql':
                                $query->$method(\DB::raw('lower(' . $column . '::text)'), 'like', "%" . strtolower($value) . '%');
                                break;

                            default:
                                $query->$method(\DB::raw('lower(' . $column . ')'), 'like', "%" . strtolower($value) . '%');
                                break;
                        }
                    }
                }
            }
        });
    }

    /**
     * Get the query clause.
     *
     * @method getClause
     *
     * @param bool $inclusive [description]
     * @param bool $isArray   [description]
     *
     * @return string the clause to use
     */
    protected function getClause($inclusive, $isArray = false)
    {
        if ($isArray) {
            $method = $inclusive ? 'orWhereIn' : 'whereIn';
        } else {
            $method = $inclusive ? 'orWhere' : 'where';
        }

        return $method;
    }

    /**
     * Filter content using the filter parameters.
     *
     * @method scopeFilter
     *
     * @param [type] $query  [description]
     * @param [type] $params [description]
     *
     * @return [type] [description]
     */
    public function scopeFilter($query, $params = [])
    {
        $params = $this->extractFilter($params) ?: $params;
        if (is_array($params) && count($params)) {
            $class = get_class($query->getModel());
            $reflection = new \ReflectionClass($class);
            // If we're filtering we most likely want exclusivity
            foreach ($params as $type => $value) {
                switch ($type) {
                    case 'sort':
                        // Order can be sent as -field | field OR array
                        $value = (array) $value;
                        if (count($value) > 1) {
                            $value = in_array($value, ['asc', 'desc']) ? 'id' : $value[0];
                        }
                        $value = array_pop($value);
                        $order = is_array($value) && !empty($value) ? array_pluck($value, 'order') : array_get($params, 'order', null);
                        $order = $order ? $order : ($value[0] === '-' ? 'desc' : null);
                        $order = $order ? $order : 'desc';
                        $value = $value[0] === '-' ? substr($value, 1) : $value;
                        $query->searchSortRelation($value, $order);
                        break;

                    case 'withDeleted':
                        $this->withTrashed();
                        break;

                    case 'strict':
                        if (\Nitm\Helpers\ModelHelper::boolval($value) == true) {
                            $query->strictExclusivity();
                        } else {
                            $query->strictInclusivity();
                        }
                        break;

                    case 's':
                        if (!empty($value)) {
                            $columns = $query->getModel()->getTableColumns();
                            $query->addStringSearchCondition($columns, $value, false);
                        }
                        break;

                    default:
                        if ($this->searchEnableStrictExclusivity) {
                            $query->exclusive();
                        } else {
                            $query->inclusive();
                        }
                        $column = $type;
                        $parts = explode('.', $type);
                        $relation = $type;
                        if (count($parts) > 1) {
                            $relation = array_shift($parts);
                        }
                        $relation = Str::camel(str_replace(['_'], '-', $relation));
                        $type = Str::studly(str_replace(['.', '_'], '-', $type));
                        $filter = 'FilterBy' . $type;
                        $scopeFilter = 'scope' . $filter;
                        $class = get_class($query->getModel());
                        if ($reflection->hasMethod($scopeFilter)) {
                            call_user_func_array([$query, Str::camel($filter)], [$value]);
                        } elseif (
                            $class::isFilterableRelation($type, $class)
                            && $reflection->hasMethod($type)
                            && $query->getModel()->$type() instanceof Relation
                        ) {
                            $query->searchFilterRelation($type, $value);
                        } elseif (
                            $class::isFilterableRelation($type, $class)
                            && $reflection->hasMethod($relation)
                            && $query->getModel()->$relation() instanceof Relation
                        ) {
                            $query->searchFilterRelation($relation, $value, implode('.', $parts) ?? 'id');
                        } else {
                            $query->addSearchCondition($column, $value);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Simple filter relation helper
     *
     * @param Builder $query
     * @param string $relation
     * @param mixed $value
     * @param string $property
     *
     * @return void
     */
    public function scopeSearchFilterRelation($query, string $relation, $value, $property = 'id')
    {
        $relationQuery = $query->getModel()->$relation();
        $filter = function ($query) use ($property, $value) {
            $primaryKey = $query->getModel()->getKeyName();

            if ($query->getModel()->hasColumn($primaryKey)) {
                $query->selectRaw($query->qualifyColumn($primaryKey));
            }

            if ($property === true) {
                if (ModelHelper::usesTrait('Nitm\Traits\Search', get_class($query->getModel()))) {
                    $query->cannotFilterRelations()->search($value);
                }
            } else {
                $qualifiedProperty = $query->qualifyColumn($property);
                if (is_array($value)) {
                    $ids = array_map(function ($v) use ($property) {
                        if (is_object($v) && property_exists($v, $property)) {
                            return $v->$property;
                        } elseif (is_array($v)) {
                            return array_get($v, $property);
                        } else {
                            return $v;
                        }
                    }, $value);

                    $query->whereIn($qualifiedProperty, array_filter($ids));
                } elseif (is_object($value) && property_exists($value, $property)) {
                    $property = $qualifiedProperty;
                    if (is_string($value) && !is_numeric($value)) {
                        $query->where(\DB::raw("lower($qualifiedProperty)"), 'like', "%{$value->$property}");
                    } else {
                        $query->where($qualifiedProperty, $value->$property);
                    }
                } elseif (is_array($value)) {
                    $query->where($qualifiedProperty, array_get($value, $property));
                } elseif (is_string($value) && !is_numeric($value)) {
                    return $query->where(\DB::raw("lower($qualifiedProperty)"), 'like', "%{$value}%");
                } elseif (is_scalar($value)) {
                    return $query->where($qualifiedProperty, $value);
                }
            }
        };

        if ($relationQuery instanceof MorphTo) {
            $method = $this->searchEnableInclusivity ? 'orWhereHasMorph' : 'whereHasMorph';
            $query->$method(
                $relation,
                '*',
                $filter
            );
        } else {
            $method = $this->searchEnableInclusivity ? 'orWhereHas' : 'whereHas';
            $query->$method(
                $relation,
                $filter
            );
        }
    }

    /**
     * Filter by a relation or by the field
     *
     * @param [type] $query
     * @param string $column
     * @param string $direction
     *
     * @return void
     */
    public function scopeSearchSortRelation($query, $column = 'id', $direction = 'desc')
    {
        $model = $query->getModel();
        $table = $model->getTable();
        $class = get_class($model);
        $reflection = new \ReflectionClass($class);
        $parts = explode('.', $column);
        $relation = Str::camel(str_replace('_', "", array_shift($parts)));
        $scopeSort = 'SortBy' . Str::studly(str_replace('.', '-', $column));
        if ($reflection->hasMethod("scope$scopeSort")) {
            call_user_func_array([$query, $scopeSort], [$column, $direction]);
        } elseif (count($parts) && $reflection->hasMethod($relation) && $query->getModel()->$relation() instanceof Relation) {
            $column = array_pop($parts);
            $sortParams = $class::getFilterDefinition();
            $relationQuery = $this->$relation();
            $localKey = $query->qualifyColumn(
                array_get($sortParams, 'localKey') ?? $relationQuery->getForeignKeyName()
            );
            if ($relationQuery instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                $resolvedFk = $relationQuery->getOwnerKeyName();
            } elseif ($relationQuery instanceof Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                $resolvedFk = $relationQuery->getParentKeyName();
            } else {
                $resolvedFk = $relationQuery->getLocalKeyName();
            }
            $foreignKey = $relationQuery->qualifyColumn(
                array_get($sortParams, 'foreignKey') ?? $resolvedFk
            );
            $table = $relationQuery->getModel()->getTable();
            $alias = $table . '_' . strtolower(str_random(3));
            if (!DbHelper::isJoinedOn($query, $table)) {
                if (is_callable($localKey)) {
                    $query->join($table, function ($join) use ($alias, $localKey) {
                        $localKey($query, $alias);
                    });
                } else {
                    $query->join(
                        $table,
                        function ($join) use ($localKey, $foreignKey, $alias, $table) {
                            $localKey = (array) $localKey;
                            $foreignKey = (array) $foreignKey;
                            $on = [array_pop($localKey) => array_pop($foreignKey)];
                            $foreignParts = explode('.', current($on));
                            $join->on($this->qualifyColumn(key($on)), '=', "$table." . array_pop($foreignParts));
                            if (count($localKey)) {
                                foreach ($localKey as $index => $lk) {
                                    $qualifiedForeign = implode('.', collect([$table])->merge(explode('.', $foreignKey[$index]))->unique()->all());
                                    $join->whereRaw("{$this->qualifyColumn($lk)} = {$qualifiedForeign}");
                                }
                            }
                        }
                    );
                }
            }
            $query->getQuery()->orders = null;
            $query->orderBy($table . "." . $column, $direction);
        } else {
            $query->addSearchOrderBy($column, $direction);
        }
    }

    /**
     * Order the results according to the provided field.
     *
     * @param Builder       $query     The query object
     * @param string||array $column     The columns to order by
     * @param string        $direction The direction order by
     */
    public function scopeAddSearchOrderBy($query, $column = null, $direction = 'desc')
    {
        //Let's limit the direction options
        $direction = $direction == 'desc' || $direction === true ? 'desc' : 'asc';
        if ($column == 'new') {
            $column = 'created_at';
            $direction = 'desc';
        }

        if ($column = $this->ensureSearchOrderBy($query, $column)) {
            $query->getQuery()->orders = null;
            $table = $query->getModel()->getTable();
            $parts = explode('.', $column);
            $realColumn = array_pop($parts);
            $qualified = $query->getModel()->hasColumn($realColumn) ? implode('.', array_unique(array_merge([$table], $parts, [$realColumn]))) : $column;
            $query->orderBy($qualified, $direction);
        }
    }

    /**
     * Detremine the column ot sort by
     *
     * @param [type] $query
     * @param string $field
     *
     * @return boolean | string
     */
    protected function ensureSearchOrderBy($query, $field = 'id')
    {

        // If id was specified but there's no id column don't sort
        $modelKey = $query->getModel()->getKeyName();
        if ($field == 'id' && $modelKey != 'id' || ($modelKey == 'id' && !$query->getModel()->hasColumn($modelKey))) {
            $field = $modelKey;
            return empty($field) || !$query->getModel()->hasColumn($field) ? false : $field;
        }
        return $field;
    }

    /**
     * Add a limit to the query.
     *
     * @param Builder $query The query object
     * @param int     $limit The max numebr of results
     */
    public function scopeAddSearchLimit($query, $limit)
    {
        $limit = (int) $limit < 100 ? $limit : 100;
        $query->take($limit);
    }

    /**
     * Disable pagination
     *
     * @param [type] $query
     * @return void
     */
    public function scopeWithoutPagination($query)
    {
        $this->searcnEnablePagination = false;
        return $query;
    }

    /**
     * Enable pagination
     *
     * @param [type] $query
     * @return void
     */
    public function scopeWithPagination($query)
    {
        $this->searcnEnablePagination = true;
        return $query;
    }

    /**
     * Enable pagination
     *
     * @param [type] $query
     * @return void
     */
    public function scopeCanFilterRelations($query)
    {
        $this->searchEnableRelationFiltering = true;
        return $query;
    }

    /**
     * Enable pagination
     *
     * @param [type] $query
     * @return void
     */
    public function scopeCannotFilterRelations($query)
    {
        $this->searchEnableRelationFiltering = false;
        return $query;
    }

    /**
     * Enable  search inclusivity
     * Basically use Or clauses
     *
     * @param [type] $query
     * @return void
     */
    public function scopeInclusive($query)
    {
        $this->searchEnableInclusivity = true;
        return $query;
    }

    /**
     * Disable search inclusivity
     * Basically DON'T use Or clauses
     *
     * @param [type] $query
     * @return void
     */
    public function scopeExclusive($query)
    {
        $this->searchEnableInclusivity = false;
        return $query;
    }

    /**
     * Enable search  directinclusivity
     * Basically use Or clauses
     *
     * @param [type] $query
     * @return void
     */
    public function scopeDirectInclusivity($query)
    {
        $this->searchEnableDirectInclusivity = true;
        return $query;
    }

    /**
     * Disable search direct inclusivity
     * Basically DON'T use Or clauses
     *
     * @param [type] $query
     *
     * @return void
     */
    public function scopeDirectExclusivity($query)
    {
        $this->searchEnableDirectInclusivity = false;
        return $query;
    }

    /**
     * Enable search  directinclusivity
     * Basically use Or clauses
     *
     * @param [type] $query
     * @return void
     */
    public function scopeStrictInclusivity($query)
    {
        $this->searchEnableStrictInclusivity = true;
        return $query;
    }

    /**
     * Disable search direct inclusivity
     * Basically DON'T use Or clauses
     *
     * @param [type] $query
     *
     * @return void
     */
    public function scopeStrictExclusivity($query)
    {
        $this->searchEnableStrictInclusivity = false;
        return $query;
    }
}
