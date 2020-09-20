<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Content\Http\Controllers\Traits;

use Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use InfyOm\Generator\Utils\ResponseUtil;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

trait CustomControllerTrait
{
    protected $model;

    protected $perPage = 10;

    public function paginate(Request $request, $query)
    {
        $page = abs(intval($request->get('page')));

        $perPage = abs(intval($request->get('perPage', $this->perPage)));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $paginator->status = 'ok';

        $fields = request()->input('_fields');
        $relations = request()->input('_relations');
        $allFields = array_merge((array) $fields, (array) $relations);

        $this->beforePaginateTransform($request, $paginator);

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
     * Do some custom pagination for paginated data
     *
     * @param Request $request
     * @param mixed $paginator
     *
     * @return [type]
     */
    protected function beforePaginateTransform(Request $request, LengthAwarePaginator $paginator)
    {
    }

    public function sendResponse($result, $message, $code = 200)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result), $code);
    }

    public function sendError($result, $message, $code = 400)
    {
        return Response::json(ResponseUtil::makeError($message, $result), $code);
    }

    protected function printSuccess($data, string $status = 'ok', int $code = 200)
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
     * @return void
     */
    protected function printModelSuccess($model, $status = 'ok', int $code = 200)
    {
        if (!empty($allWith = (array) request()->input('_with'))) {
            foreach ($allWith as $with) {
                try {
                    $model->load($with);
                } catch (\Exception $e) {
                }
            }
        } else {
            if (method_exists($model, 'getCustomWith')) {
                $model->load($model->getAllWith());
                $model->loadCount($model->getAllWithCount());
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
     * @param Builder $builder
     * @param Model|string|int $model
     * @param string $key
     * @param boolean $silently
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
}