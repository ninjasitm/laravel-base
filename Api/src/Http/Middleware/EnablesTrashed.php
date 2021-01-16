<?php

namespace Nitm\Api\Http\Middleware;

use App\Team;

class EnablesTrashed
{
    /**
     * THis middleware will enable trashed scope resolution for route binding
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $userModel = config('nitm-api.user_model');
        if(!class_exists($userModel)) {
            throw new \Error('Unable to find user model for trashed search middleware');
        }
        $userModel::addGlobalScope(
            'trashedUser', function ($builder) {
                $builder->withTrashed();
            }
        );

        return $next($request);
    }
}