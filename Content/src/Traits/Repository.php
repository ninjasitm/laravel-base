<?php

namespace Nitm\Content\Traits;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Nitm\Content\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Nitm\Content\Traits\RepositorySyncsRelations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

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
    protected bool $updateExisting = true;

    /**
     * Update using the given keys
     *
     * @var array
     */
    protected array $updateExistingKeys;

    /**
     * Allow the user to define the fields to be returned
     *
     * @return array
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
     * @return Collection|Paginator|LengthAwarePaginator
     */
    public static function collectionToArray($collection): Collection|Paginator|LengthAwarePaginator
    {
        $fields = request()->input('_fields');
        $relations = request()->input('_relations');
        $allFields = array_merge((array) $fields, (array) $relations);
        if (!empty($allFields)) {
            $transformer = function ($data) use ($allFields) {
                if ($data instanceof Model || is_array($data)) {
                    $realData = $data instanceof Model ? $data->toArray() : $data;
                    return Arr::only($realData, $allFields);
                } else {
                    return $data;
                }
            };
            if ($collection instanceof LengthAwarePaginator || $collection instanceof Paginator) {
                $collection->getCollection()->transform($transformer);
            } else {
                $collection->transform($transformer);
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
    public function makeModel(): ?Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Get Meta Input
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return void
     */
    public function getMetaInput($key, $default = null)
    {
        $value = request()->input($key);
        if (!$value) {
            return $default;
        }
        return is_array($value) ? $value : (json_decode($value, true) ?? $value);
    }

    /**
     * Paginate records for scaffold.
     *
     * @param  int   $perPage
     * @param  array $columns
     * @return LengthAwarePaginatorContract
     */
    public function paginate($perPage, $columns = ['*']): ?LengthAwarePaginatorContract
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Paginate the given query using the request
     *
     * @param  mixed $request
     * @param  mixed $query
     * @param  string $using The paginator mathod to use
     * @return LengthAwarePaginatorContract|CursorPaginatorContract|PaginatorContract
     */
    public function paginateUsing(Request $request, $query, $using = 'paginate', $perPage = null, $columns = ['*'], $name = 'page', $position = null)
    {
        $page = abs($position ?: intval($request->get('page')));

        $perPage = abs($perPage ?: intval($request->get('perPage', 10)));

        if (!empty($allWith = (array) $this->getMetaInput('_with'))) {
            $query->with(array_filter($allWith, [$query->getModel(), 'hasRelation']));
        }

        $using = in_array(strtolower($using), ['paginate', 'simplepaginate', 'cursorpaginate']) ? $using : 'paginate';
        if(strtolower($using) === 'cursorpaginate') {
            // The 4th argument to cursorPaginate is a cursor and is notably different from simplePaginate and paginate
            $paginator = $query->cursorPaginate($perPage, $columns, $name);
        } else {
            $paginator = $query->$using($perPage, $columns, $name, $page);
        }

        $paginator->status = 'ok';

        $fields = $this->getMetaInput('_fields');
        $relations = $this->getMetaInput('_relations');
        $allFields = array_merge((array) $fields, (array) $relations);

        if (!empty($allFields)) {
            $paginator->getCollection()->transform(
                function ($data) use ($allFields) {
                    if ($data instanceof Model || is_array($data)) {
                        $realData = $data instanceof Model ? $data->toArray() : $data;
                        return Arr::only($realData, $allFields);
                    } else {
                        return $data;
                    }
                }
            );
        }

        return $paginator;
    }

    /**
     * Search for data on the model
     *
     * @param  array $data
     * @return Builder
     */
    public function search($data = []): ?Builder
    {
        return $this->model->search($data);
    }

    /**
     * Search for data on the model
     *
     * @param  array $data
     * @return Builder
     */
    public function trashedSearch($data = []): ?Builder
    {
        return $this->model->search($data)->withTrashed();
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param  array    $search
     * @param  int|null $skip
     * @param  int|null $limit
     * @return Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null): ?Builder
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
     * @return Collection|static[]
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*']): ?Collection
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
    public function create($input): ?Model
    {
        return DB::transaction(function () use ($input) {
            if ($this->updateExisting) {
                // Some input may need to be transformed by the model
                $keys = empty($this->updateExistingKeys) ? $this->model->getFillable() : $this->updateExistingKeys;
                $attributes = Arr::only($this->model->newInstance($input)->fill($input)->getAttributes(), $keys);
                $model = $this->model->firstOrNew($attributes);
            } else {
                $model = $this->model->newInstance($input);
            }

            $model->fill(Arr::only($input, $model->getFillable()));

            $model->save();

            $this->syncData($model, $input);

            return $model->refresh();
        });
    }

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model|null
     */
    public function find($id, $columns = ['*']): ?Model
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
     * @throws ModelNotFoundException
     *
     * @return Model
     */
    public function findOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model
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
     * @throws ModelNotFoundException
     *
     * @return bool
     */
    public function existsOrFail($id, $key = 'id', $silently = false): ?bool
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
     * @throws ModelNotFoundException
     *
     * @return Model
     */
    public function trashedFindOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model
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
     * @return bool
     */
    public function trashedExistsOrFail($id, $key = 'id', $silently = false): ?bool
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
     * @return Model
     */
    public function update($input, $model): ?Model
    {
        return DB::transaction(function () use ($input, $model) {
            if (!($model instanceof Model)) {
                $query = $this->model->newQuery();
                $model = $query->findOrFail($model);
            }

            $model->fill($input);

            $model->save();

            $this->syncData($model, $input);

            return $model->refresh();
        });
    }

    /**
     * @param int|Model $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($model): ?bool
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
