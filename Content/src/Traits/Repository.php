<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Nitm\Content\Traits\RepositorySyncsRelations;
use Nitm\Content\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Traits for Model.
 */
trait Repository
{
    use RepositorySyncsRelations;

    /**
     * Update the existing if it already exists
     *
     * @var bool
     */
    protected $updateExisting = true;

    /**
     * Allow the user to define the fields to be returned
     *
     * @return void
     */
    public function toArray()
    {
        $data = parent::toArray();
        $fields = request()->input('_fields');
        $relations = (array) (request()->input('_relations') ?: []);
        $relations = array_map(
            function ($relation, $index) {
                return is_numeric($index) ? $relation : $index;
            },
            $relations,
            array_keys($relations)
        );
        $allFields = array_merge((array) $fields, (array) $relations);

        if (!empty($allFields)) {
            $allFields = array_merge(
                $allFields,
                array_map(
                    function ($field) {
                        $field = explode('.', $field)[0];
                        return Str::snake($field);
                    },
                    $allFields
                )
            );
            $data = Arr::only($data, $allFields);
        }
        return $data;
    }

    /**
     * Allow the user to define the fields to return for the collection
     *
     * @param  Collection|Paginator|LengthAwarePaginator $collection
     * @return void
     */
    public static function collectionToArray($collection)
    {
        $fields = request()->input('_fields');
        $relations = request()->input('_relations');
        $allFields = array_merge((array) $fields, (array) $relations);
        if (!empty($allFields)) {
            $transofmer = function ($data) use ($allFields) {
                if ($data instanceof Model || is_array($data)) {
                    $realData = $data instanceof Model ? $data->toArray() : $data;
                    return Arr::only($realData, $allFields);
                } else {
                    return $data;
                }
            };
            if ($collection instanceof LengthAwarePaginator || $collection instanceof Paginator) {
                $collection->getCollection()->transform($transform);
            } else {
                $collection->transform($transform);
            }
        }
        return $collection;
    }



    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param  int   $perPage
     * @param  array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'])
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Search for data on the model
     *
     * @param  array $data
     * @return Builder
     */
    public function search($data = [])
    {
        return $this->model->search($data);
    }

    /**
     * Search for data on the model
     *
     * @param  array $data
     * @return Builder
     */
    public function trashedSearch($data = [])
    {
        return $this->model->search($data)->withTrashed();
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param  array    $search
     * @param  int|null $skip
     * @param  int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null)
    {
        $query = $this->model->search($search);

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array    $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array    $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model Return an up to date fresh model
     */
    public function create($input)
    {
        if ($this->updateExisting) {
            // Some input may need to be transformed by the model
            $attributes = Arr::only($this->model->newInstance($input)->fill($input)->getAttributes(), $this->model->getFillable());
            $model = $this->model->firstOrCreate($attributes);
        } else {
            $model = $this->model->newInstance($input);
        }

        $model->fill(Arr::only($input, $model->getFillable()));

        $model->save();

        $this->syncData($model, $input);

        return $model->refresh();
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->model->newQuery();

        if ($relations = request()->input('_relations')) {
            $query->with($relations);
        }

        if ($id instanceof Model && $id->exists) {
            return $id;
        }

        if ($id instanceof Model) {
            $id = $id->id;
        }

        if (empty($id)) {
            throw new NotFoundHttpException("Missing value for id");
        }

        if (is_callable($id)) {
            $query->where($id);
        } else if (is_numeric($id)) {
            $query->where('id', $id);
        } else if (is_string($id)) {
            if ($key == 'id' && $this->model->hasTrait('\Nitm\Content\Traits\SetUuid')) {
                $query->whereUuid($id);
            } else {
                $query->where('id', (int)$id);
            }
        } elseif (is_array($id) || is_callable($id)) {
            $query->where($id);
        }
        return $query->get($columns)->first();
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function findOrFail($id, $columns = ['*'], $key = 'id', $silently = false)
    {
        if ($id instanceof Model && $id->exists) {
            return $id;
        }

        if ($id instanceof Model) {
            $id = $id->id;
        }

        $query = $this->model->newQuery();

        $exists = $this->existsOrFail($id, $key, $silently);

        if (!$exists && $silently) {
            return false;
        }

        if (is_callable($id)) {
            $query->where($id);
        } else if (is_numeric($id)) {
            $query->where($key, $id);
        } else if (is_string($id)) {
            if ($key == 'id' && $this->model->hasTrait('\Nitm\Content\Traits\SetUuid')) {
                $query->whereUuid($id);
            } else {
                $query->where($key, (int)$id);
            }
        } elseif (is_array($id) || is_callable($id)) {
            $query->where($id);
        }
        return $query->get($columns)->first();
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function existsOrFail($id, $key = 'id', $silently = false)
    {
        $query = $this->model->newQuery();

        $id = is_object($id) ? $id->id : $id;
        if (is_numeric($id)) {
            $query->where($key, $id);
        } else if (is_string($id)) {
            if ($key == 'id' && $this->model->hasTrait('\Nitm\Content\Traits\SetUuid')) {
                $query->whereUuid($id);
            } else {
                $query->where($key, (int)$id);
            }
        } elseif (is_array($id)) {
            $query->where($id);
        } else {
            throw new \Exception('Invalid type for id. Expecting one of [string, integer, boolean, float, array]. Received ' . gettype($id));
        }
        if (!$query->exists()) {
            if ($silently) {
                return false;
            }
            throw new ModelNotFoundException;
        }

        return true;
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function trashedFindOrFail($id, $columns = ['*'], $key = 'id', $silently = false)
    {
        $query = $this->model->newQuery();

        $exists = $this->trashedExistsOrFail($id, $key, $silently);

        if (!$exists) {
            return false;
        }

        return $query->withTrashed()->find($id, $columns);
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function trashedExistsOrFail($id, $key = 'id', $silently = false)
    {
        $query = $this->model->newQuery()->withTrashed();

        $id = is_object($id) ? $id->id : $id;
        if (is_numeric($id)) {
            $query->where($key, $id);
        } else if (is_string($id)) {
            if ($key == 'id' && $this->model->hasTrait('\Nitm\Content\Traits\SetUuid')) {
                $query->whereUuid($id);
            } else {
                $query->where($key, $id);
            }
        } elseif (is_array($id)) {
            $query->where($id);
        } else {
            throw new \Exception('Invalid type for id. Expecting one of [string, integer, boolean, float, array]. Received ' . gettype($id));
        }
        if (!$query->exists()) {
            if ($silently) {
                return false;
            }
            throw new ModelNotFoundException;
        }

        return true;
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int   $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $model)
    {
        if (!($model instanceof Model)) {
            $query = $this->model->newQuery();
            $model = $query->findOrFail($model);
        }

        $model->fill($input);

        $model->save();

        $this->syncData($model, $input);

        return $model->refresh();
    }

    /**
     * @param int|Model $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($model)
    {
        if (!($model instanceof Model)) {
            $query = $this->model->newQuery();
            $model = $query->findOrFail($model);
        }

        return $model->delete();
    }

    /**
     * Sync the model's data
     *
     * @param array $data
     *
     * @return void
     */
    public function syncData($model, array $data)
    {
    }

    /**
     * Import models
     *
     * @param array $data
     *
     * @return array
     */
    public function import(array $data): array
    {
        return [
            'hasError' => false,
            'models' => []
        ];
    }

    /**
     * Prepare the form config
     *
     * @return array
     */
    public function prepareFormConfig(Team $team, Request $request): array
    {
        return [];
    }

    /**
     * Prepare the index config
     *
     * @return array
     */
    public function prepareIndexConfig(Team $team, Request $request): array
    {
        return [
            'filters' => []
        ];
    }
}