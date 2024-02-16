<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Nitm\Content\Models\Metadata;
use Nitm\Helpers\CollectionHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait HasMetadata
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasMetadata()
    {
        $this->addCustomWith(
            'metadata'
        );
    }

    /**
     * Delete each metadata individually
     *
     * @return integer
     */
    public function deleteMetadata()
    {
        return $this->metadata()->get()->reduce(
            function ($result, $metadata) {
                return $metadata->delete() ? $result + 1 : $result;
            }, 0
        );
    }

    public function metadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        $class = '\\Nitm\Content\\Models\\Metadata\\Metadata';
        $baseClass = class_basename(get_class($this));
        if (!class_exists('\\Nitm\Content\\Models\\Metadata\\' . $baseClass . 'Metadata')) {
            $class = Metadata::class;
        }
        return $this->morphMany($class, 'entity')
            ->where('entity_relation', 'metadata')
            ->byPriority();
    }

    public function requiredMetadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->metadata()->whereIsRequired(true);
    }

    public function missingMetadata(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->requiredMetadata()->isMissingValue();
    }



    /**
     * Sync single metadata
     * TODO: Why do I have two methods that do the same thing?
     *
     * @param  array  $data
     * @param  string $key
     * @return Model
     */
    public function syncSingleMetadata($data, string $key)
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
            if ($this->relationLoaded($relation)) {
                $model = $this->$relation ?? new Metadata;
            } else {
                $model = $id ? $this->$relation()->find($id) : new Metadata;
            }

            if ($model instanceof EloquentCollection) {
                $model = $model->firstWhere('id', '=', $id) ?? new Metadata;
            }
        }
        if (Schema::hasColumn($model->getTable(), 'priority') && !$model->priority) {
            $model->priority = $this->$relation()->count();
        }
        $model->fill($data);
        if (!$model->exists) {
            $this->saveRelation($key, $model);
        } else {
            $model->save();
        }
        if (Schema::hasColumn($model->getTable(), 'priority') && $model->priority) {
            $this->$relation()->where('priority', '>', $model->priority)->increment('priority', 1);
        }
        return $model;
    }

    /**
     * Sync single metadata
     *
     * @param  array  $data
     * @param  string $key
     * @return Model
     */
    public function syncMetadataModel($data, string $key)
    {
        return $this->syncSingleMetadata($data, $key);
    }

    /**
     * Sync metadata
     * TODO: Update syncMetadata usage to require the actual data directly instead of in a nested array
     *
     * @param  array   $data
     * @param  string  $key
     * @param  callable $callable
     * @param  boolean $dataIsValue
     * @return Illuminate\Support\Collection
     */
    public function syncMetadata($data, string $key = 'metadata', callable $callable = null, array $linkedBy = ['id'])
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
            if (CollectionHelper::isCollection($data)) {
                $data = $data->filter()->all();
            }

            if (empty($this->$relation)) {
                $this->load($relation);
            }
            foreach ($data as $idx => $entry) {
                if (isset($entry['deleted']) && isset($entry['id'])) {
                    $toDelete[$idx] = $entry;
                } else {
                    $toSync[$idx] = $entry;
                }
            }

            if ($toDelete->count()) {
                $this->$relation()->whereIn($this->$relation()->getModel()->getTable() . '.id', $toDelete->pluck('id')->all())->delete();
            }

            // Ensure what what needs to be synced is not duplicated
            if ($toSync->pluck('linked_metadata_id')->filter()->count()) {
                // Get the existing linked metadata
                $existingLinkedMetadata = $this->$relation->pluck('linked_metadata_id');

                //Now merge it with the toSync data to ensure it's not duplicated
                $toSync->transform(function ($entry) use ($existingLinkedMetadata, $relation) {
                    $linkedMetadataId = Arr::get($entry, 'linked_metadata_id');
                    if (!empty($linkedMetadataId)) {
                        // Do this on the loaded relation instead of hitting the DB again
                        $existingLinkedMetadata = $this->$relation->firstWhere('linked_metadata_id', $linkedMetadataId);
                        if ($existingLinkedMetadata instanceof Metadata) {
                            $entry = array_merge($existingLinkedMetadata->toArray(), $entry);
                        }
                    }
                    return $entry;
                });
            }

            if ($toSync->count()) {
                foreach ($toSync->filter()->values() as $index => $metadata) {
                    $metadata['priority'] = $index;
                    $metadata['entity_relation'] = $relation;
                    $metadata['created_at'] = $metadata['updated_at'] = Carbon::now();
                    $where = $this->_getLinkCondition($metadata, $linkedBy);
                    $model = $this->_findRelationModel($relation, $where, $metadata);
                    $model->fill($metadata);
                    $model->save();

                    if (
                        method_exists($model, 'syncData')
                        && is_callable([$model, 'syncData'])
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
            $this->load($relation);
        }
        return $this->$relation;
    }
}