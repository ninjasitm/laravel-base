<?php

namespace Nitm\Api\Http\Middleware;

use Illuminate\Support\Arr;

class ResolvesTeam
{
    /**
     * This function properly sets the team request value for the route
     * There is an issue with receiving a team array from the client
     * When this happens, it can cause problems with functions that depend on team route binding
     * There are currently no logical reasons to attach the team route
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $teamModel = config('nitm-api.team_model');
        if(!class_exists($teamModel)) {
            throw new \Error('Unable to find team model for team resolver middleware');
        }
        $parameters = collect($request->route()->parameters());
        $user = $request->user() ?: auth()->user();

        $team = $parameters->get('team') ?? ($user ? $user->team : null);
        if (!($team instanceof $teamModel) && !empty($value)) {
            if (is_array($team)) {
                \Log::warning("Route parameter {team} was an array. The client should not set the team variable on team route requests");
                $team = (new $teamModel)->resolveRouteBinding(Arr::get($team, 'id')) ?? abort(404);
                $request->request->add(['team' => $team]);
            } elseif (is_numeric($team)) {
                $team = (new $teamModel)->resolveRouteBinding($team);
                $request->request->add(['team' => $team]);
            }
            $request->route()->setParameter('team', $team);
        }

        return $next($request);
    }
}