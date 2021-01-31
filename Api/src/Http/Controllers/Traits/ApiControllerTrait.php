<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Api\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiControllerTrait
{

    /**
     * Do some custom pagination for paginated data using resources if available
     *
     * @param Request $request
     * @param mixed   $paginator
     *
     * @return [type]
     */
    protected function beforePaginateTransform(Request $request, LengthAwarePaginator $paginator)
    {
        if(class_exists($this->resource())) {
            $class = $this->resource();
            $paginator->setCollection($class::collection($paginator->getCollection())->collection);
        } else {
            parent::beforePaginateTransform($request, $paginator);
        }
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
        if(class_exists($this->resource()) && !is_a($model, $this->resource())) {
            $class = $this->resource();
            $model = new $class($model);
        }

        return parent::printModelSuccess($model, $status, $code);
    }
}