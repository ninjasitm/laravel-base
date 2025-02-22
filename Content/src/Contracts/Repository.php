<?php

namespace Nitm\Content\Contracts;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Nitm\Content\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator as Paginator;
use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginator;

interface Repository
{
    /**
     * Sync the model's data
     *
     * @param iterable$data
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
     * @param int   $perPage
     * @param iterable$columns
     * @return LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*']): ?LengthAwarePaginator;

    /**
     * Get Meta Input
     *
     * @param mixed $key
     * @param mixed $default
     * @return void
     */
    public function getMetaInput($key, $default = null);

    /**
     * Paginate the given query using the request
     *
     * @param mixed $request
     * @param mixed $query
     * @return LengthAwarePaginator|CursorPaginator|Paginator
     */
    public function paginateUsing(Request $request, $query);

    /**
     * Build a query for retrieving all records.
     *
     * @param iterable   $search
     * @param int|null $skip
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null);

    /**
     * Retrieve all records with given filter criteria
     *
     * @param iterable   $search
     * @param int|null $skip
     * @param int|null $limit
     * @param iterable   $columns
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
     * @param Collection|Paginator|LengthAwarePaginator $collection
     * @return Collection|Paginator|LengthAwarePaginator
     */
    public static function collectionToArray($collection);

    /**
     * Import models
     *
     * @param iterable$data
     * @return array
     */
    public function import(array $data): array;

    /**
     * Search the models
     *
     * @param iterable$data
     * @return Builder     *
     */
    public function search(array $data): ?Builder;

    /**
     * Search the trashed models
     *
     * @param iterable$data
     * @return Builder
     */
    public function trashedSearch(array $data): ?Builder;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param iterable$columns
     *
     * @return Model|null
     */
    public function find($id, $columns = ['*']): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param iterable$columns
     *
     * @return Model|null
     */
    public function findOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param iterable$columns
     *
     * @return bool
     */
    public function existsOrFail($id, $key = 'id', $silently = false): ?bool;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param iterable$columns
     *
     * @return Model|null
     */
    public function trashedFindOrFail($id, $columns = ['*'], $key = 'id', $silently = false): ?Model;

    /**
     * Find model record for given id
     *
     * @param int   $id
     * @param iterable$columns
     *
     * @return bool
     */
    public function trashedExistsOrFail($id, $key = 'id', $silently = false): ?bool;

    /**
     * Create a new model
     *
     * @param iterable$data
     * @return Model     *
     */
    public function create(array $data): ?Model;

    /**
     * Update a new model
     *
     * @param iterable$data
     * @return Model     *
     */
    public function update(Collection|array $input, Model $model): ?Model;

    /**
     * Update a new model
     *
     * @param mixed $model
     * @return bool
     *
     */
    public function delete(Model $modell): ?bool;
}
