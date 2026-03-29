<?php
namespace Nitm\Content\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nitm\Helpers\CollectionHelper;
use Nitm\Helpers\DbHelper;

trait SyncsRelations {
    protected function getSyncableRelations() {
        return [];
    }

    /**
     * Extract Real Data From
     *
     * @param mixed $keys
     * @param mixed $data
     * @return void
     */
    protected function extractRealDataFrom($keys, $data) {
        $realData = null;
        $keys     = (array) $keys;
        foreach ($keys as $key) {
            $realData = Arr::get($data, $key, null);
            if (! empty($realData)) {
                break;
            }
        }
        return $realData;
    }

    /**
     * Sync the relation by determining the type and calling the appropriate function
     *
     * @param iterable$data
     *
     * @param string $relation
     *
     * @return mixed
     */
    public function syncRelation($data, string $relation,  ? callable $callable = null, $linkedBy = ['id']) {
        $syncRelation = 'sync' . Str::studly($relation);
        if (method_exists($this, $syncRelation)) {
            return $this->$syncRelation($data, $relation, $callable, $linkedBy);
        }

        $relationType = $this->getRelationType($relation);
        switch ($relationType) {
        case 'hasMany' :
            return $this->syncHasOneOrManyRelation($data, $relation, $callable, $linkedBy);
            break;
        case 'belongsTo':
            return $this->syncBelongsToRelation($data, $relation);
            break;
        case 'manyToMany':
            return $this->syncManyToManyRelation($data, $relation, $callable);
            break;
        case 'hasOne':
            return $this->syncHasOneRelation($data, $relation, $callable, $linkedBy);
            break;
        default:
            $data = ['data' => Arr::get($data, 'data') ?: $data];
            return $this->syncRelationData($relation, 'data', $data);
            break;
        }
    }

    /**
     * Get the type of relation
     *
     * @param string $relation
     *
     * @return string
     */
    public function getRelationType(string $relation) {
        $relationType = 'metadata';
        if (method_exists($this, $relation)) {
            $relationInstance = $this->$relation();
            if ($relationInstance instanceof HasOneOrMany || $relationInstance instanceof HasManyThrough) {
                $relationType = 'hasMany';
            } else if ($relationInstance instanceof BelongsTo) {
                $relationType = 'belongsTo';
            } else if ($relationInstance instanceof BelongsToMany) {
                $relationType = 'manyToMany';
            } else {
                $relationType = 'hasOne';
            }
        }

        return $relationType;
    }

    /**
     * Sync Relation Data
     *
     * @param mixed $relation
     * @param mixed $key
     * @param mixed $data
     * @return void
     */
    public function syncRelationData(string $relation, $key, $data) {
        $key        = (array) $key;
        $realData   = $this->extractRealDataFrom($key, $data);
        $realData   = CollectionHelper::isCollection($realData) ? $realData : collect((array) $realData);
        $syncMethod = Str::camel('sync-' . $relation);
        if (method_exists($this, $syncMethod)) {
            $this->$syncMethod($realData->toArray(), $relation);
        } else {
            $filteredData = $realData->map(
                function ($v) {
                    if (is_object($v)) {
                        return $v->id;
                    }
                    if (is_array($v)) {
                        return Arr::get($v, 'id');
                    }
                    return $v;
                }
            )->filter(
                function ($v, $k) {
                    return filter_var($v, FILTER_VALIDATE_INT);
                }
            );
            $this->$relation()->sync($filteredData->toArray());
        }
        return $this->$relation;
    }

    /**
     * Sync Relation Data
     *
     * @param mixed $relation
     * @param mixed $key
     * @param mixed $data
     * @return void
     */
    public function syncRelationDataWithParams(string $relation, $key, $data, $orderBy = 'updated_at') {
        $this->syncBelongsToManyRelation($relation, $key, $data, $orderBy);
    }

    /**
     * Sync belongs to many Relation Data
     *
     * @param mixed $relation
     * @param mixed $key
     * @param mixed $data
     * @return void
     */
    public function syncBelongsToManyRelation(string $relation, $key, $data, $orderBy = 'updated_at') {
        $pivotFields   = [];
        $realData      = $this->extractRealDataFrom($key, $data);
        $realData      = CollectionHelper::isCollection($realData) ? $realData : collect((array) $realData);
        $relationQuery = $this->$relation();
        if ($realData->count()) {
            $syncMethod = Str::camel('sync-' . $relation);
            if (method_exists($this, $syncMethod)) {
                $this->$syncMethod($realData->toArray(), $relation);
            } else {
                $filteredData = [];
                foreach ($realData as $key => $params) {
                    $id = null;
                    if (is_array($params) && ! empty($id = Arr::pull($params, 'id'))) {
                        $filteredData[$id] = [];
                    }
                    if ($key !== 'id') {
                        $id                = $id ?: (is_numeric($params) ? $params : $key);
                        $filteredData[$id] = is_array($params) ? $params : [];
                    }
                }
                $pivotFields = array_keys((array) current($filteredData));
                if ($relationQuery instanceof HasMany || $relationQuery instanceof HasManyThrough) {
                    $relationQuery->whereIn('id', array_keys($filteredData))
                        ->get()
                        ->map(function ($model) use ($filteredData) {
                            $model->fill($filteredData[$model->id]);
                            $model->save();
                        });
                } else {
                    $relationQuery->sync($filteredData);
                }
            }
        }

        if ($relationQuery instanceof HasMany || $relationQuery instanceof HasManyThrough) {
            $relationQuery = $this->$relation()->orderByRaw("$orderBy asc" . (app()->environment('testing') ? '' : ' NULLS LAST'));
        } else {
            $relationQuery = empty($pivotFields) ? $this->$relation() : $this->$relation()->withPivot($pivotFields);
            if (! empty($pivotFields) && in_array($orderBy, $pivotFields)) {
                $relationQuery->orderByRaw("pivot_{$orderBy}" . (app()->environment('testing') ? '' : ' NULLS LAST'));
            }
        }

        return $relationQuery->get();
    }

    /**
     * Sync a relation
     *
     * @param iterable   $data
     * @param string   $key
     * @param callback $callback A method that can be used to transform a single entry
     * @param iterable   $linkedBy
     * @return \Illuminate\Support\Collection | null
     */

    public function syncHasOneOrManyRelation(iterable | Collection $data, string $relation,  ? callable $callable = null, $linkedBy = ['id']) {
        $data = is_array($data) ? array_filter($data) : $data;

        if (
            (is_array($data) && empty($data))
            && ($data instanceof Collection && ! $data->count())
        ) {
            return;
        }

        $toSync   = collect([]);
        $toDelete = collect([]);

        if (! empty($data)) {
            if ($data instanceof Collection) {
                $data = $data->filter()->all();
            } else if (is_array($data)) {
                $data = array_filter($data);
            } else {
                $data = [$data];
            }
            // if (empty($this->$relation)) {
            //     $this->load($relation);
            // }
            foreach ($data as $idx => $entry) {
                if (isset($entry['deleted']) && isset($entry['id'])) {
                    $toDelete[$idx] = $entry;
                } else {
                    $toSync[$idx] = $entry;
                }
            }

            if (count($toDelete)) {
                $this->$relation()->whereIn('id', $toDelete->pluck('id')->all())->delete();
            }

            if ($toSync->count()) {
                foreach ($toSync->filter()->values() as $index => $newData) {
                    if (is_callable($callable)) {
                        $newData = $callable($newData, $index, $this);
                    }
                    $where    = $this->_getLinkCondition($newData, $linkedBy);
                    $method   = is_object($newData) ? 'save' : 'create';
                    $model    = $this->_findRelationModel($relation, $where, $newData, $method);
                    $fillData = is_object($newData) ? $newData->getAttributes() : $newData;
                    $model->fill($fillData);
                    $model->save();

                    if (
                        method_exists($model, 'syncData')
                        && is_callable([$model, 'syncData'])
                    ) {
                        $model->syncData($fillData);
                    }
                    $syncedModels[$model->id] = $model;
                }
            }
            /**
             * TODO: Verify that this properly loads relations.
             * May need to $this->load($relation) instead to get all related records
             */
            $this->load($relation);
        }
        return $this->$relation;
    }

    /**
     * Sync the belongs to relation for the given model
     *
     * @param iterable $data
     * @param string $relation
     *
     * @return Model
     */
    public function syncBelongsToRelation($data, string $relation) {
        if (empty($data)) {
            return;
        }

        $relationInstance = $this->$relation();
        $relationModel    = $relationInstance->getModel();
        $modelClass       = get_class($relationModel);
        $key              = $relationInstance->getForeignKeyName();
        $data             = is_object($data) ? $data->getAttributes() : $data;
        $id               = Arr::get($data, 'id') ?: (Arr::isList($data) ? Arr::first($data) : null);
        $model            = $this->$relation;
        // If the model doesn't exist then create it using the given data
        if (empty($model)) {
            $requiredFields = DbHelper::getColumns($relationModel->getTable())->filter(function ($column) {
                return $column->getNotnull();
            })->filter(fn($column) => $column->getName() != 'id')->pluck('name')->all();
            $model = $id ? $modelClass::find($id) : $modelClass::firstOrCreate(Arr::only($data, $requiredFields));
        }

        if ($model) {
            $this->$key = $model->id;
            $this->save();
        }
        return $model;
    }

    /**
     * Sync a belongs to many relation
     *
     * @param iterable   $data
     * @param string   $key
     * @param callback $callback            A method that can be used to transform a single entry
     * @param boolean  $detachBeforeSyncing
     * @return \Illuminate\Support\Collection
     */

    public function syncManyToManyRelation($data, string $relation,  ? callable $callable = null, $detachBeforeSyncing = true) {
        $data = is_array($data) ? array_filter($data) : $data;

        if (
            (is_array($data) && empty($data)) ||
            (CollectionHelper::isCollection($data) && ! $data->count())
        ) {
            return;
        }

        if (! empty($data)) {
            if (CollectionHelper::isCollection($data)) {
                $data = $data->filter()->all();
            } else {
                $data = array_filter($data);
            }

            if (! $detachBeforeSyncing) {
                $this->$relation()->syncWithoutDetatching()->sync($data);
            } else {
                $this->$relation()->sync($data);
            }
            /**
             * TODO: Verify that this properly loads relations.
             * May need to $this->load($relation) instead to get all related records
             */
            $this->load($relation);
        }
        return $this->$relation;
    }

    /**
     * Sync a single relation
     *
     * @param iterable   $data
     * @param string   $key
     * @param callback $callback A method that can be used to transform a single entry
     * @param iterable   $linkedBy
     * @return Model
     */
    public function syncSingleRelation($data, string $relation,  ? callable $callable = null, ?array $linkedBy = null) {
        return $this->syncHasOneRelation($data, $relation, $callable, $linkedBy);
    }

    /**
     * Sync a relation
     *
     * @param iterable   $data
     * @param string   $key
     * @param callback $callback A method that can be used to transform a single entry
     * @param iterable   $linkedBy
     * @return Model
     */

    public function syncHasOneRelation($data, string $relation,  ? callable $callable = null, ?array $linkedBy = null) {

        if (
            (! is_array($data) && is_object($data) && ! method_exists($data, 'getAttributes')) || (is_array($data) && empty($data))
        ) {
            return;
        }

        if (! empty($data)) {
            if (empty($this->$relation)) {
                $this->load($relation);
            }

            if (is_callable($callable)) {
                $data = $callable($data, $this);
            }
            $method = is_object($data) ? 'save' : 'create';
            if (! empty($linkedBy)) {
                $where = $this->_getLinkCondition($data, $linkedBy);
                $model = $this->_findRelationModel($relation, $where, $data, $method);
            } else {
                $model = $this->_findRelationModel($relation, [], $data, $method);
            }
            $fillData = is_object($data) ? $data->getAttributes() : $data;
            $model->fill($fillData);
            $model->save();

            if (
                method_exists($model, 'syncData')
                && is_callable([$model, 'syncData'])
            ) {
                $model->syncData($fillData);
            }
            $this->setRelation($relation, $model);
        }
        return $this->$relation;
    }

    /**
     * Get the link condition for data
     *
     * @param object $data
     * @param iterable $linkedBy Can be an associataive array or an indexed array
     * @return array
     */
    protected function _getLinkCondition($data, array $linkedBy) : array {
        $data = $data instanceof Model ? $data->getAttributes() : $data;
        $id   = Arr::get($data, 'id');

        if (empty($id) && is_array($data)) {
            $keys  = Arr::isAssoc($linkedBy) ? array_keys($linkedBy) : $linkedBy;
            $where = array_filter(Arr::only($data, $keys));
            if (Arr::isAssoc($linkedBy)) {
                $where = array_filter(array_merge($where, array_filter($linkedBy)));
            }
        } else {
            $where = ['id' => $id];
        }

        return $where;
    }

    /**
     * Find a relational model
     *
     * @param string      $relation
     * @param iterable      $where
     * @param array|Model $data
     * @param string      $method
     * @return Model
     */
    protected function _findRelationModel(string $relation, array $where, $data = null, $method = 'create') {
        $existing = null;
        $query    = $this->$relation();
        if (! empty($where)) {
            foreach ($where as $key => $value) {
                $table = $query->getModel()->getTable();
                $query->where(strpos($key, '.') === false ? $table . '.' . $key : $key, $value);
            }
            return $query->setEagerLoads([])->first() ?? $this->$relation()->$method($data);
        }

        $existing = $this->$relation;
        return $existing instanceof Model ? $existing : $this->$relation()->$method($data);
    }
}
