<?php

/**
 * Custom traits for API controllers
 *
 * @category API
 * @package  Nitm\Api\Http\Controllers\Traits
 * @author   Malcolm Paul <malcolm@ninjasitm.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://gitlab.com/ninjasitm/api
 */

namespace Nitm\Api\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Trait ApiControllerTrait
 *
 * Custom traits for API controllers
 */
trait ApiControllerTrait
{

    /**
     * Do some custom pagination for paginated data using resources if available
     *
     * @param Request                                        $request   The HTTP request object
     * @param LengthAwarePaginator|CursorPaginator|Paginator $paginator The paginator instance
     *
     * @return void
     */
    protected function beforePaginateTransform(Request $request, LengthAwarePaginator|CursorPaginator|Paginator $paginator)
    {
        if (class_exists($this->resource())) {
            $class = $this->resource();
            $paginator->setCollection($class::collection($paginator->items())->collection);
        } else {
            parent::beforePaginateTransform($request, $paginator);
        }
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
        if (class_exists($this->resource()) && !is_a($model, $this->resource())) {
            $class = $this->resource();
            $model = new $class($model);
        }

        return parent::printModelSuccess($model, $status, $code);
    }
}
