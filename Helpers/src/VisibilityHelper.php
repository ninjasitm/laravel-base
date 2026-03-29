<?php
namespace Nitm\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * String helper class
 */
class VisibilityHelper {

    /**
     * Undocumented function
     *
     * @param [type] $callback
     * @param [type] ...$variadic
     *
     * @return void
     */
    public static function limitAccessForNonAdmins($query, callable $callback, ...$variadic) {
        $args         = array_pop($variadic);
        $args         = is_array($args) ? $args : [];
        $args['user'] = $args['user'] ?? Auth::user();

        if (! is_object($args['user']) || ! method_exists($args['user'], 'isApprovedOn') || ! method_exists($args['user'], 'isAdminOn')) {
            $query->whereId(-1);
            return true;
        }

        $args['team'] = isset($args['team']) ? $args['team'] : (request()->team ?: $args['user']->team);

        if ($args['user']->isApprovedOn($args['team']) && $args['user']->isAdminOn($args['team'])) {
            return true;
        }

        array_unshift($args, $query);

        call_user_func_array($callback, array_values($args));
    }
}
