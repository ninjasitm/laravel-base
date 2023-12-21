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
use Illuminate\Http\Response;
use Nitm\Content\Jobs\RecordActivity;

trait CustomControllerTrait
{
    /**
     * The model that should be used for pagination.
     *
     * @var string
     */
    protected $model;

    /**
     * The model that should be used for pagination.
     *
     * @var string
     */
    protected $perPage = 10;

    /**
     * Enable activity recording for this controller
     *
     * @var bool
     */
    protected $recordsActivity = false;

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
        if (strtolower($using) === 'cursorpaginate') {
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
     * Make a response and append meta if needed.
     *
     * @param array $data
     * @param string $message
     *
     * @return Response
     */
    protected function makeResponse($data, $message = 'Success!')
    {
        $result = $this->appendMeta(ResponseUtil::makeResponse($message, $data));
        return $result;
    }

    /**
     * Send Response
     *
     * @param  mixed $result
     * @param  mixed $message
     * @param  mixed $code
     * @return string | Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
        // 15 = Symfony\Component\HttpFoundation::DEFAULT_ENCODING_OPTIONS
        return response()->json($this->makeResponse($result, $message), $code, [], 15 | JSON_INVALID_UTF8_SUBSTITUTE);
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
        return response()->json(ResponseUtil::makeError($message, $result), $code);
    }

    /**
     * Get Meta Input
     *
     * @param  mixed $key
     * @param  mixed $default
     * @return void
     */
    protected function getMetaInput($key, $default = null)
    {
        $value = request()->input($key);
        if (!$value) {
            return $default;
        }
        return is_array($value) ? $value : (json_decode($value, true) ?? $value);
    }

    /**
     * Add Meta information to the response
     *
     * @param  mixed $meta
     * @return void
     */
    protected function addMeta($meta = [])
    {
        $this->responseMeta = array_merge($this->responseMeta, $meta);
    }

    /**
     * Set Meta information for the response
     *
     * @param  mixed $meta
     * @return void
     */
    protected function setMeta($meta = [])
    {
        $this->responseMeta = $meta;
    }

    /**
     * Append meta to the data
     *
     * @param  mixed $data
     * @return void
     */
    protected function appendMeta($data)
    {
        if (!empty($this->responseMeta)) {
            if ($data instanceof Collection || $data instanceof Paginator || $data instanceof Model) {
                $data = $data->toArray();
            }
            if (is_array($data)) {
                $data['meta'] = $this->responseMeta;
            }
        }
        return $data;
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
            } elseif (($model instanceof \Illuminate\Contracts\Support\Responsable) && $model->resource instanceof Model && is_callable([$model, 'getCustomWith'])) {
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

        if ($this->recordsActivity) {
            RecordActivity::dispatch(auth()->user(), $model, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
            ]);
        }

        $this->beforeSendModel(request(), $model);

        return $this->printSuccess($model, $status, $code);
    }

    /**
     * Load a model response
     *
     * @param Model $model
     *
     * @return void
     */
    protected function printModelSuccessWithMeta($model, $status = 'ok', int $code = 200)
    {
        if (is_object($model) && method_exists($model, 'getMeta')) {
            $this->addMeta($model->getMeta());
        }
        return $this->printModelSuccess($model, $status, $code);
    }

    /**
     * Adjust items on the model before sending the response
     *
     * @param  mixed $model
     * @return void
     */
    protected function beforeSendModel(Request $request, $model)
    {
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
        return $this->paginate(request(), $items);
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
            throw new ModelNotFoundException();
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
