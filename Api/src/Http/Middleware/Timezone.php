<?php namespace Nitm\Api\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Timezone
{

    /**
     * The current logged in user instance
     *
     * @var Nitm\Content\User
     */
    protected $user;

    /**
     * Creates an instance of the middleware
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->user = $auth->user();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->setTimezone($request);
        return $this->addTimezoneCookie($request, $next($request));
    }

    /**
     * Sets the time zone from cookie or from the user setting
     *
     * @param Illuminate\Http\Request $request
     */
    public function setTimezone($request)
    {
        /**
         * Automatically update the user's timezone based on where there are located
         */

        $user = $request->user();
        if ($user) {
            $timezone = $request->header('X-Timezone') ?? $user->timezone ?? config('app.timezone');
            if ($timezone != $user->timezone) {
                $user->timezone = $timezone;
                $user->save();
            }

            if ($timezone) {
                return date_default_timezone_set($timezone);
            }
        } else {
            $timezone = $request->header('X-Timezone') ?? config('app.timezone');
            return date_default_timezone_set($timezone);
        }
    }

    /**
     * Adds the cookie and header to response
     *
     * @param Illuminate\Http\Request  $request
     * @param Illuminate\Http\Response $response
     */
    public function addTimezoneCookie($request, $response)
    {
        $requestTimezone = $request->header('X-Timezone');
        $timezone = $this->user ? ($this->user->timezone ?? $requestTimezone) : $requestTimezone;
        if ($timezone) {
            // Sometimes, due to Passport, we get the wrong response instance
            if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
                $response->headers->set('X-Timezone', $timezone);
                $response->headers->setCookie(cookie('X-Timezone', $timezone, 120));
                return $response;
            } else {
                return $response->withCookie(cookie('X-Timezone', $timezone, 120))
                    ->withHeaders(
                        [
                        'X-Timezone' => $timezone
                        ]
                    );
            }
        }
        return $response;
    }
}
