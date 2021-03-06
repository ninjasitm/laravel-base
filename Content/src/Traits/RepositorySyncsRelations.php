<?php

namespace Nitm\Content\Traits;

use Schema;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Nitm\Content\Models\Metadata\Metadata;

trait RepositorySyncsRelations
{
    protected function getSyncableRelations()
    {
        return [];
    }

    public function syncRelationData(Model $subject, string $relation, $inputData, $data)
    {
        $realData = Arr::get($data, $inputData, null);
        if (is_array($realData)) {
            $syncMethod = Str::camel('sync-' . $relation);
            if (method_exists($this, $syncMethod)) {
                $this->$syncMethod($subject, $realData ?: [], $relation);
            } else {
                $filteredData = collect($realData)->map(function ($v) {
                    if (is_object($v)) {
                        return $v->id;
                    }
                    if (is_array($v)) {
                        return Arr::get($v, 'id');
                    }
                    return $v;
                })->filter(function ($v, $k) {
                    return filter_var($v, FILTER_VALIDATE_INT);
                });
                $subject->$relation()->sync($filteredData->toArray());
            }
        }
    }

    /**
     * SYnc a many to many relation with the new data
     *
     * @param [type] $data
     * @param [type] $relation
     * @return void
     */
    public function syncManyToManyRelation(Model $subject, $data, string $relation)
    {
        $subject->$relation()->detach();
        $subject->$relation()->attach($data);
    }

    public function syncDeliverables(Model $subject, $data, string $relation = 'deliverables', string $typeKey = 'deliverable_type')
    {
        $data = array_filter((array)$data);

        $sync = [];
        foreach ($data as $id) {
            $sync[$id] = [
                $typeKey => Str::singular($relation),
                "deliverable_relations_id" => $subject->id,
                'deliverable_id' => $id
            ];
        }
        /**
         * Need to do this here because otherwise
         * when using the sync or detach methods
         * it doesn't take into effect deliverable_relations_type
         * */
        \DB::table('deliverable_relations')
            ->where([
                'deliverable_type' => Str::singular($relation),
                "deliverable_relations_id" => $subject->id,
            ])
            ->delete();
        if (!empty($sync)) {
            $subject->$relation()->attach($sync);
        }
    }

    /**
     * Sync single metadata
     * TODO: Why do I have two methods that do the same thing?
     *
     * @param array $data
     * @param string $key
     * @return Model
     */
    public function syncSingleMetadata(Model $subject, $data, string $key)
    {
        // print_r($data);

        if (empty($data)) {
            return;
        }

        $relation = Str::camel($key);

        if (is_object($data) && $data->id) {
            $model = $data;
            $model->entity_relation = $model->entity_relation ?? $relation;
        } else {
            $data = is_object($data) ? $data->getAttributes() : $data;
            $data['entity_relation'] = $relation;
            $id = Arr::get($data, 'id');
            if ($subject->relationLoaded($relation)) {
                $model = $subject->$relation ?? new Metadata;
            } else {
                $model = $id ? $subject->$relation()->find($id) : new Metadata;
            }

            if ($model instanceof EloquentCollection) {
                $model = $model->firstWhere('id', '=', $id) ?? new Metadata;
            }
        }
        if (Schema::hasColumn($model->getTable(), 'priority') && !$model->priority) {
            $model->priority = $subject->$relation()->count();
        }
        $model->fill($data);
        if (!$model->exists) {
            $subject->saveRelation($key, $model);
        } else {
            $model->save();
        }
        if (Schema::hasColumn($model->getTable(), 'priority') && $model->priority) {
            $subject->$relation()->where('priority', '>', $model->priority)->increment('priority', 1);
        }
        return $model;
    }

    /**
     * Sync single metadata
     *
     * @param array $data
     * @param string $key
     * @return Model
     */
    public function syncMetadataModel(Model $subject, $data, string $key)
    {
        return $this->syncSingleMetadata($model, $data, $key);
    }

    /**
     * Sync metadata
     * TODO: Update syncMetadata usage to require the actual data directly instead of in a nested array
     * @param array $data
     * @param string $key
     * @param boolean $dataIsValue
     * @return Illuminate\Support\Collection
     */
    public function syncMetadata(Model $subject, $data, string $key = 'metadata', array $linkedBy = ['id'])
    {
        $data = array_filter((array)$data);
        if (!is_array($data) || empty($data)) {
            return;
        }
        $snakeKey = Str::snake($key);
        $relation = Str::camel($key);

        if (isset($data[$key])) {
            $data = $data[$key];
        } elseif (isset($data[$snakeKey])) {
            $data = $data[$snakeKey];
        }

        $syncedModels = collect([]);
        $toSync = collect([]);
        $toDelete = collect([]);

        if (!empty($data)) {
            if ($data instanceof Collection || $data instanceof EloquentCollection) {
                $data = $data->filter()->all();
            }

            if (empty($subject->$relation)) {
                $subject->load($relation);
            }
            foreach ($data as $idx => $entry) {
                if (isset($entry['deleted']) && isset($entry['id'])) {
                    $toDelete[$idx] = $entry;
                } else {
                    $toSync[$idx] = $entry;
                }
            }

            if (count($toDelete)) {
                $subject->$relation()->whereIn('id', $toDelete->pluck('id')->all())->delete();
            }
            if ($toSync->count()) {
                foreach ($toSync->filter()->values() as $index => $metadata) {
                    $metadata['priority'] = $index;
                    $metadata['entity_relation'] = $relation;
                    $metadata['created_at'] = $metadata['updated_at'] = Carbon::now();
                    $where = $this->_getLinkCondition($metadata, $linkedBy);
                    $model = $this->_findRelationModel($subject, $relation, $where, $metadata);
                    $model->fill($metadata);
                    $model->save();

                    if (
                        method_exists($model, 'syncData') &&
                        is_callable([$model, 'syncData'])
                    ) {
                        $model->syncData($metadata);
                    }
                    $syncedModels[$model->id] = $model;
                }
            }
            /**
             * TODO: Verify that this properly loads relations.
             * May need to $this->load($relation) instead to get all related records
             */
            $subject->load($relation);
        }
        return $subject->$relation;
    }


    /**
     * Sync a relation
     * @param array $data
     * @param string $key
     * @param callback $callback A method that can be used to transform a single entry
     * @param array $linkedByswe987o0ilp bn
     * @return Illuminate\Support\Collection
     */

    public function syncRelation(Model $subject, $data, string $relation, callable $callable = null, $linkedBy = ['id'])
    {
        $data = is_array($data) ? array_filter($data) : $data;

        if (
            (is_array($data) && empty($data)) &&
            (($data instanceof Collection || $data instanceof EloquentCollection) && !$data->count())
        ) {
            return;
        }

        $syncedModels = collect([]);
        $toSync = collect([]);
        $toDelete = collect([]);

        if (!empty($data)) {
            if ($data instanceof Collection || $data instanceof EloquentCollection) {
                $data = $data->filter()->all();
            } else {
                $data = array_filter($data);
            }
            // if (empty($subject->$relation)) {
            //     $subject->load($relation);
            // }
            foreach ($data as $idx => $entry) {
                if (isset($entry['deleted']) && isset($entry['id'])) {
                    $toDelete[$idx] = $entry;
                } else {
                    $toSync[$idx] = $entry;
                }
            }

            if (count($toDelete)) {
                $subject->$relation()->whereIn('id', $toDelete->pluck('id')->all())->delete();
            }

            if ($toSync->count()) {
                foreach ($toSync->filter()->values() as $index => $newData) {
                    if (is_callable($callable)) {
                        $newData = $callable($newData, $index, $this);
                    }
                    $where = $this->_getLinkCondition($newData, $linkedBy);
                    $method = is_object($newData) ? 'save' : 'create';
                    $model = $this->_findRelationModel($subject, $relation, $where, $newData, $method);
                    $fillData = is_object($newData) ? $newData->getAttributes() : $newData;
                    $model->fill($fillData);
                    $model->save();

                    if (
                        method_exists($model, 'syncData') &&
                        is_callable([$model, 'syncData'])
                    ) {
                        $model->syncData($fillData);
                    }
                    $syncedModels[$model->id] = $model;
                }
            }
            /**
             * TODO: Verify that this properly loads relations.
             * May need to $subject->load($relation) instead to get all related records
             */
            $subject->load($relation);
        }
        return $subject->$relation;
    }

    /**
     * Sync a relation
     * @param array $data
     * @param string $key
     * @param callback $callback A method that can be used to transform a single entry
     * @param array $linkedBy
     * @return Illuminate\Support\Collection
     */

    public function syncSingleRelation(Model $subject, $data, string $relation, callable $callable = null, array $linkedBy = null)
    {
        if (
            (!is_array($data) && is_object($data) && !method_exists($data, 'getAttributes')) || (is_array($data) && empty($data))
        ) {
            return;
        }

        if (!empty($data)) {
            if (empty($subject->$relation)) {
                $subject->load($relation);
            }

            if (is_callable($callable)) {
                $data = $callable($data, $index, $this);
            }
            if (!empty($linkedBy)) {
                $where = $this->_getLinkCondition($data, $linkedBy);
                $method = is_object($data) ? 'save' : 'create';
                $model = $this->_findRelationModel($subject, $relation, $where, $data, $method);
            } else {
                $model = $this->_findRelationModel($subject, $relation, [], null, $save);
            }
            $fillData = is_object($data) ? $data->getAttributes() : $data;
            $model->fill($fillData);
            $model->save();

            if (
                method_exists($model, 'syncData') &&
                is_callable([$model, 'syncData'])
            ) {
                $model->syncData($fillData);
            }
            $subject->setRelation($relation, $model);
        }
        return $subject->$relation;
    }

    /**
     * Get the link condition for data
     *
     * @param object $data
     * @param array $linkedBy Can be an associataive array or an indexed array
     * @return array
     */
    protected function _getLinkCondition($data, array $linkedBy): array
    {
        $data = $data instanceof Model ? $data->getAttributes() : $data;
        $id = Arr::get($data, 'id');

        if (empty($id) && is_array($data)) {
            $keys = Arr::isAssoc($linkedBy) ? array_keys($linkedBy) : $linkedBy;
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
     * @param string $relation
     * @param array $where
     * @param array|Model $data
     * @param string $method
     * @return Model
     */
    protected function _findRelationModel(Model $subject, string $relation, array $where, $data = null, $method = 'create')
    {
        $existing = null;
        $query = $subject->$relation();
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
            return $query->first() ?? $subject->$relation()->$method($data);
        }

        $existing = $subject->$relation;
        return $existing instanceof Model ? $existing : $subject->$relation()->$method($data);
    }
}
