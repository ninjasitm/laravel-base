<?php

namespace Nitm\Api\Http\Middleware;

use Carbon\Carbon;

class UpdatesUserActivity
{
    /**
     * Properly update the last hactivity for the user
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $user = $request->user() ?: auth()->user();

        if($user && $user->isFillable('last_active')) {
            $user->last_active = Carbon::now();
            $user->save();
        }

        return $next($request);
    }
}