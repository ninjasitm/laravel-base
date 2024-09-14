<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Content\Http\Controllers\Traits;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Nitm\Content\Models\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Nitm\Content\Jobs\RecordActivity;
use Illuminate\Database\Query\Builder;
use InfyOm\Generator\Utils\ResponseUtil;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * The meta information to be returned with the response
     *
     * @var array
     */
    protected $responseMeta = [];

    /**
     * Use full pagination
     *
     * @param Request $request The request object
     * @param mixed $query The query builder instance.
     *
     * @return LengthAwarePaginator|CursorPaginator|Paginator|array
     */
    public function paginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'paginate');
    }

    /**
     * Use Cursor Pagination
     *
     * @param Request $request The request object
     * @param mixed $query The query builder instance.
     *
     * @return LengthAwarePaginator|CursorPaginator|Paginator|array
     */
    public function cursorPaginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'cursorPaginate');
    }

    /**
     * Use simple pagination
     *
     * @param Request $request The request object
     * @param mixed $query The query builder instance.
     *
     * @return LengthAwarePaginator|CursorPaginator|Paginator|array
     */
    public function simplePaginate(Request $request, $query)
    {
        return $this->afterPaginate($request, $query, 'simplePaginate');
    }

    /**
     * A function that is used to paginate the data.
     *
     * @param Request $request The request object.
     * @param mixed $query The query
     * @param string $using The method to use to paginate the results.
     * @param int|null $perPage The number of items to show per page.
     * @param array $columns The columns to be selected.
     * @param string $name The name of the paginator instance.
     * @param int|null $position The page number to be returned.
     *
     * @return LengthAwarePaginator|CursorPaginator|Paginator|array A paginator result
     */
    public function afterPaginate(Request $request, $query, string $using = 'paginate', $perPage = null, $columns = ['*'], $name = 'page', $position = null): LengthAwarePaginator|CursorPaginator|Paginator|array
    {
        $page = $position ?: abs(intval($request->get('page')));
        $perPage = $perPage ?: abs(intval($request->get('perPage', $this->perPage)));
        if (strtolower($using) === 'cursorpaginate') {
            // The 4th argument to cursorPaginate is a cursor and is notably different from simplePaginate and paginate
            $paginator = $query->cursorPaginate($perPage, $columns, $name);
        } elseif ($query instanceof Paginator || $query instanceof CursorPaginator || $query instanceof LengthAwarePaginator) {
            $paginator = $query;
        } else {
            $paginator = $query->$using($perPage, $columns, $name, $page);
        }

        $paginator->status = 'ok';

        $this->beforePaginateTransform($request, $paginator);

        $fields = request()->input('_fields');
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
     * @param LengthAwarePaginator|CursorPaginator|Paginator   $paginator
     *
     * @return void
     */
    protected function beforePaginateTransform(Request $request, LengthAwarePaginator|CursorPaginator|Paginator $paginator)
    {
    }

    /**
     * Make a response and append meta if needed.
     *
     * @param array $data
     * @param string $message
     *
     * @return array
     */
    protected function makeResponse($data, $message = 'Success!')
    {
        $result = $this->appendMeta(ResponseUtil::makeResponse($message, $data));
        return $result;
    }

    /**
     * Send Response
     *
     * @param mixed $result
     * @param mixed $message
     * @param mixed $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message, $code = 200)
    {
        // 15 = Symfony\Component\HttpFoundation::DEFAULT_ENCODING_OPTIONS
        return response()->json($this->makeResponse($result, $message), $code, [], 15 | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * It returns a json response with the error message and the error code.
     *
     * @param mixed $result The result of the operation.
     * @param string message The message you want to send to the user.
     * @param int code HTTP status code
     *
     * @return \Illuminate\Http\JsonResponse
     * ```
     */
    public function sendError($result, $message, $code = 400)
    {
        return response()->json(ResponseUtil::makeError($message, is_array($result) ? $result : [$result]), $code);
    }

    /**
     * Get Meta Input
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
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
     * @param mixed $meta
     * @return void
     */
    protected function addMeta($meta = [])
    {
        $this->responseMeta = array_merge($this->responseMeta, $meta);
    }

    /**
     * Set Meta information for the response
     *
     * @param mixed $meta
     * @return void
     */
    protected function setMeta($meta = [])
    {
        $this->responseMeta = $meta;
    }

    /**
     * Append meta to the data
     *
     * @param mixed $data
     * @return array
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
     * @param mixed $data The data to be returned.
     * @param string $status The status of the response.
     * @param int $code The HTTP status code to return.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function printSuccess(mixed $data, string $status = 'ok', int $code = 200)
    {
        $fields = request()->input('_fields');
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
     * @return \Illuminate\Http\JsonResponse
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
            } elseif (($model instanceof \Illuminate\Contracts\Support\Responsable) && property_exists($model, 'resource') && $model->resource instanceof Model && is_callable([$model, 'getCustomWith'])) {
                try {
                    $allWith = $model->resource->getAllWith();
                    if (!empty($allWith)) {
                        $model->resource->load($allWith);
                    }

                    $allWithCount = $model->resource->getAllWithCount();
                    if (!empty($allWithCount)) {
                        $model->resource->loadCount($allWithCount);
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
     * @return \Illuminate\Http\JsonResponse
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
     * @param Request $request
     * @param mixed $model
     * @return void
     */
    protected function beforeSendModel(Request $request, $model)
    {
        // Consider adding logic here if needed
    }


    /**
     * Load a collection response
     *
     * @param Collection $model
     *
     * @return LengthAwarePaginator|CursorPaginator|Paginator|array
     */
    protected function printSuccessCollection(Collection $items)
    {
        return $this->paginate(request(), new LengthAwarePaginator(['items' => $items], $items->count(), $this->perPage));
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
     * @param mixed $user
     * @param mixed $model
     * @param mixed $property
     *
     * @return bool
     */
    protected function userOwnsOrFail(Authenticatable $user, Model|EloquentModel $model, string $property = null)
    {
        if (!$this->userOwns($user, $model, $property)) {
            abort(403);
        }
        return true;
    }

    /**
     * User Owns Or Fail
     *
     * @param mixed $user
     * @param mixed $model
     * @param mixed $property
     *
     * @return bool
     */
    protected function userOwns(Authenticatable $user, Model|EloquentModel $model, string $property = null)
    {
        $property = $property ?? (property_exists($model, 'author_id') ? 'author_id' : 'user_id');
        if ($user->getAuthIdentifier() == $model->$property) {
            return true;
        }
        return false;
    }
}
