<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Content\Http\Controllers\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InfyOm\Generator\Utils\ResponseUtil;
use Response;

trait CustomControllerTrait
{
    protected $model;

    protected $perPage = 10;

    /**
     * Use full pagination
     *
     * @param Request request The request object
     * @param query The query builder instance.
     *
     * @return The return value of the afterPaginate method.
     */
    public function paginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'paginate');
    }

    /**
     * Use Cursor Pagination
     *
     * @param Request request The request object
     * @param query The query builder instance.
     *
     * @return Paginator A paginator object
     */
    public function cursorPaginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'cursorPaginate');
    }

    /**
     * Use simple pagination
     *
     * @param Request request The request object
     * @param query The query builder instance.
     *
     * @return The return value is the result of the afterPaginate method.
     */
    public function simplePaginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'simplePaginate');
    }

    /**
     * A function that is used to paginate the data.
     *
     * @param Request request The request object.
     * @param Query The query
     * @param using The method to use to paginate the results.
     * @param perPage The number of items to show per page.
     * @param columns The columns to be selected.
     * @param name The name of the paginator instance.
     * @param position The page number to be returned.
     *
     * @return LengthAwarePaginatorContract | CursorPaginatorContract | PaginatorContract | array A paginator result
     */
    public function afterPaginate(Request $request, $query, $using = 'paginate', $perPage = null, $columns = ['*'], $name = 'page', $position = null): LengthAwarePaginatorContract | CursorPaginatorContract | PaginatorContract | array
    {
        $page      = $position ?: abs(intval($request->get('page')));
        $perPage   = $perPage ?: abs(intval($request->get('perPage', $this->perPage)));
        if(strtolower($using) === 'cursorpaginate') {
            // The 4th argument to cursorPaginate is a cursor and is notably different from simplePaginate and paginate
            $paginator = $query->cursorPaginate($perPage, $columns, $name);
        } else {
            $paginator = $query->$using($perPage, $columns, $name, $page);
        }

        $paginator->status = 'ok';

        $this->beforePaginateTransform($request, $paginator);

        $fields    = request()->input('_fields');
        $relations = request()->input('_relations');
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

        $chunk = intval(request()->input('_chunk'));
        if ($chunk > 1) {
            // Work around for chunking cursor pagination
            // TODO: See if Laravel resolves this
            if (strtolower($using) == 'cursorpaginate') {
                $result = $paginator->toArray();
                $result['data'] = $paginator->getCollection()->chunk($chunk)->map(
                    function ($group) {
                        return $group->values();
                    }
                );
                $paginator = $result;
            } else {
                $paginator->setCollection($paginator->getCollection()->chunk($chunk)->map(
                    function ($group) {
                        return $group->values();
                    }
                ));
            }
        }

        return $paginator;
    }

    /**
     * Do some custom pagination for paginated data
     *
     * @param Request $request
     * @param LengthAwarePaginatorContract | CursorPaginatorContract | PaginatorContract   $paginator
     *
     * @return void
     */
    protected function beforePaginateTransform(Request $request, LengthAwarePaginatorContract | CursorPaginatorContract | PaginatorContract $paginator)
    {
    }

    /**
     * It returns a JSON response with a message and a result
     *
     * @param result The data you want to return
     * @param message The message you want to send to the user.
     * @param code HTTP status code
     *
     * @return A JSON response with the message and result.
     */
    public function sendResponse($result, $message, $code = 200)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result), $code);
    }

    /**
     * It returns a json response with the error message and the error code.
     *
     * @param result The result of the operation.
     * @param message The message you want to send to the user.
     * @param code HTTP status code
     *
     * @return A JSON object with the following structure:
     * ```
     * {
     *     "error": {
     *         "message": "The error message",
     *         "code": 400
     *     }
     * }
     * ```
     */
    public function sendError($result, $message, $code = 400)
    {
        return Response::json(ResponseUtil::makeError($message, $result), $code);
    }

    /**
     * It takes the data, status, and code and returns a response with the data, status, and code
     *
     * @param data The data to be returned.
     * @param string status The status of the response.
     * @param int code The HTTP status code to return.
     *
     * @return The data is being returned with the status and code.
     */
    protected function printSuccess($data, string $status = 'ok', int $code = 200)
    {
        $fields    = request()->input('_fields');
        $relations = request()->input('_relations');
        $allFields = $fields !== 'all' ? array_merge((array) $fields, (array) $relations) : [];
        if (!empty($allFields)) {
            $allFields = array_merge($allFields, array_map('Str::snake', $allFields));
            if (is_array($data)) {
                $data = Arr::only($data, $allFields);
            } elseif ($data instanceof Model) {
                $data = Arr::only($data->toArray(), $allFields);
            }
        }
        return $this->sendResponse($data, $status, $code);
    }

    /**
     * Load a model response
     *
     * @param Model $model
     *
     * @return void
     */
    protected function printModelSuccess($model, $status = 'ok', int $code = 200)
    {
        if ($model instanceof Model && !empty($allWith = (array) request()->input('_with'))) {
            foreach ($allWith as $with) {
                try {
                    $model->load($with);
                } catch (\Exception $e) {
                }
            }
        } else {
            if ($model instanceof Model && is_callable([$model, 'getCustomWith'])) {
                try {
                    $allWith = $model->getAllWith();
                    if (!empty($allWith)) {
                        $model->load($allWith);
                    }

                    $allWithCount = $model->getAllWithCount();
                    if (!empty($allWithCount)) {
                        $model->loadCount($allWithCount);
                    }
                } catch (\Exception $e) {
                }
            } else if (($model instanceof \Illuminate\Contracts\Support\Responsable) && $model->resource instanceof Model && is_callable([$model, 'getCustomWith'])) {
                try {
                    $allWith = $model->getAllWith();
                    if (!empty($allWith)) {
                        $model->load($allWith);
                    }

                    $allWithCount = $model->getAllWithCount();
                    if (!empty($allWithCount)) {
                        $model->loadCount($allWithCount);
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return $this->printSuccess($model, $status, $code);
    }

    /**
     * Load a collection response
     *
     * @param Collection $model
     *
     * @return void
     */
    protected function printSuccessCollection(Collection $items)
    {
        return $this->paginate($items);
    }

    /**
     * Helper method to fail if a key doesn't exist in a collection
     *
     * @param Builder          $builder
     * @param Model|string|int $model
     * @param string           $key
     * @param boolean          $silently
     *
     * @return bool
     */
    protected function existsOrFail($builder, $model, $key = 'id', $silently = false): bool
    {
        $id = is_object($model) ? $model->id : $model;
        if (!is_scalar($id) && !is_array($id)) {
            throw new \Exception('Invalid type for id. Expecting one of [string, integer, boolean, float, array]. Received ' . gettype($id));
        }

        if (!$builder->where($key, $id)->exists()) {
            if ($silently) {
                return false;
            }
            throw new ModelNotFoundException;
        }

        return true;
    }

    /**
     * User Owns Or Fail
     *
     * @param  mixed $user
     * @param  mixed $model
     * @param  mixed $property
     * @return void
     */
    protected function userOwnsOrFail(Authenticatable $user, Model $model, string $property = null)
    {
        if (!$this->userOwns($user, $model, $property)) {
            abort(403);
        }
        return true;
    }

    /**
     * User Owns Or Fail
     *
     * @param  mixed $user
     * @param  mixed $model
     * @param  mixed $property
     * @return void
     */
    protected function userOwns(Authenticatable $user, Model $model, string $property = null)
    {
        $property = $property ?? (property_exists($model, 'author_id') ? 'author_id' : 'user_id');
        if ($user->id == $model->$property) {
            return true;
        }
        return false;
    }
}
