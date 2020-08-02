<?php

namespace Nitm\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * String helper class
 */
class VisibilityHelper
{

    /**
     * Undocumented function
     *
     * @param [type] $callback
     * @param [type] ...$variadic
     *
     * @return void
     */
    public static function limitAccessForNonAdmins($query, callable $callback, ...$variadic)
    {
        $args = array_pop($variadic);
        $args['user'] = isset($args['user']) ? $args['user'] ?: auth()->user() : auth()->user();

        if (!($args['user'] instanceof User) && !is) {
            $query->whereId(-1);
            return true;
        }

        $args['team'] = isset($args['team']) ? $args['team'] : request()->team ?: $args['user']->team;

        if ($args['user']->isApprovedOn($args['team']) && $args['user']->isAdminOn($args['team'])) {
            return true;
        }

        array_unshift($args, $query);

        call_user_func_array($callback, array_values($args));
    }
}
