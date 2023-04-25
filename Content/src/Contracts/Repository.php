<?php

namespace Nitm\Content\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Nitm\Content\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

interface Repository
{
    /**
     * Sync the model's data
     *
     * @param array $data
     */
    public function syncData(BaseModel $model, array|Collection $data = []);

    /**
     * Get searchable fields array
     *
     * @return array
     */
    public function getFieldsSearchable(): ?array;

    /**
     * Configure the Model
     *
     * @return string
     */
    public function model(): string;

    /**
     * Make a Model
     *
     * @return Model
     */
    public function makeModel(): ?Model;

    /**
     * Paginate records for scaffold.
     *
     * @param  int   $perPage
     * @param  array $columns
     * @return LengthAwarePaginatorContract
     */
    public function paginate($perPage, $columns = ['*']): ?LengthAwarePaginatorContract;

    /**
     * Get Meta Input
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return void
     */
    public function getMetaInput($key, $default = null);

    /**
     * Paginate the given query using the request
     *
     * @param  mixed $request
     * @param  mixed $query
     * @return LengthAwarePaginatorContract|CursorPaginatorContract|PaginatorContract
     */
    public function paginateUsing(Request $request, $query);

    /**
     * Build a query for retrieving all records.
     *
     * @param  array    $search
     * @param  int|null $skip
     * @param  int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null);

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
    public function all($search = [], $skip = null, $limit = null, $columns = ['*']);

    /**
     * Convert the databse models to an array based on request fields
     *
     * @return array
     */
    public function toArray();

    /**
     * Convert the collection to an array based on request fields
     *
     * @param  Collection|Paginator|LengthAwarePaginator $collection
     * @return Collection|Paginator|LengthAwarePaginator
     */
    public static function collectionToArray($collection);

    /**
     * Import models
     *
     * @param  array $data
     * @return array
     */
    public function import(array $data): array;

    /**
     * Search the models
     *
     * @param  array $data
     * @return Builder     *
     */
    public function search(array $data): ?Builder;

    /**
     * Search the trashed models
     *
     * @param  array $data
     * @return Builder
     */
    public function trashedSearch(array $data): ?Builder;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return Model|null
     */
    public function find($id, $columns = ['*']): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return Model|null
     */
    public function findOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return bool
     */
    public function existsOrFail($id, $key = 'id', $silently = false): ?bool;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return Model|null
     */
    public function trashedFindOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param array $columns
     *
     * @return bool
     */
    public function trashedExistsOrFail($id, $key = 'id', $silently = false): ?bool;

    /**
     * Create a new model
     *
     * @param  array $data
     * @return Model     *
     */
    public function create(array $data): ?Model;

    /**
     * Update a new model
     *
     * @param  array $data
     * @return Model     *
     */
    public function update(Collection|array $input, Model $model): ?Model;

    /**
     * Update a new model
     *
     * @param  mixed $model
     * @return bool
     *
     */
    public function delete(Model $modell): ?bool;
}
