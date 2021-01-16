<?php

namespace Nitm\Api\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Samuel Tope <email@email.com>
 * @link   https://dev.to/samolabams/transforming-laravel-request-data-using-middleware-2k7j
 */
class SanitizesData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isJson()) {
            $this->clean($request->json());
        } else {
            $this->clean($request->request);
        }

        return $next($request);
    }
    /**
     * Clean the request's data
     *
     * @param  \Symfony\Component\HttpFoundation\ParameterBag $bag
     * @return void
     */
    private function clean(ParameterBag $bag)
    {
        $bag->replace($this->cleanData($bag->all()));
    }
    /**
     * Cleanup the code for when javascript sends undefined properties
     *
     * @param  array $data
     * @return array
     */
    private function cleanData(array $data)
    {
        return collect($data)->map(
            function ($value, $key) {
                if (is_string($value)
                    && $value === 'null'
                    || $value === 'undefined'
                    && $value !== 0
                    && $value !== '0'
                ) {
                    return null;
                }
                if (is_array($value)) {
                    return array_filter(
                        $this->cleanData($value), function ($value) {
                            return $value !== null;
                        }
                    );
                }

                return $value;
            }
        )->all();
    }
}