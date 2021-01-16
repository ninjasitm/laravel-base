<?php

namespace Nitm\Content\Traits;

use Nitm\Content\Models\User;
use Nitm\Helpers\AttachedUsersHelper;
use Illuminate\Database\Eloquent\Builder;

trait FiltersUsers
{
    // public static function bootFiltersUsers() {
    //     ModelHelper::bootRelation(User::class, 'users', [
    //         'id', 'name', 'email'
    //     ], 'user_id');
    // }

    /**
     * User query scopes
     */

    // public function scopeForUser($query, $user = null, $team = null)
    // {
    //     if (!$user) {
    //         return;
    //     }

    //     if (!$user->isApproved($team)) {
    //         $query->whereHas('applicationPipeline', function ($query) use ($user, $team) {
    //             $query->forUser($user, $team);
    //         });
    //     } else {
    //         if ($user->isStudentOn()) {
    //             $query->hasStudent($user);
    //         } elseif ($user->isMentorOn()) {
    //             $query->hasMentor($user);
    //         }
    //     }
    // }

    /**
     * Filter Users
     *
     * @param  mixed $query
     * @param  mixed $user
     * @param  mixed $property
     * @return void
     */
    public static function filterUsers($query, $user, $property)
    {
        return AttachedUsersHelper::filterUsers($query, $user, $property);
    }

    /**
     * Filter by given user
     *
     * @param Builder $query
     * @param mixed   $user
     * @param string  $property
     *
     * @return void
     */
    public function scopeHasUser($query, $user, $property = 'id')
    {
        $query->whereHas(
            'users', function ($query) use ($user, $property) {
                $query->select('users.id');
                static::filterUsers($query, $user, $property);
            }
        );
    }

    /**
     * Filter by organisation mentors
     *
     * @param Builder $query
     * @param mixed   $user
     * @param string  $property
     *
     * @return void
     */
    public function scopeHasMentor($query, $user, $property = 'id')
    {
        $query->whereHas(
            'mentors', function ($query) use ($user, $property) {
                $query->select('users.id');
                static::filterUsers($query, $user, $property);
            }
        );
    }

    /**
     * Filter by organisation students
     *
     * @param Builder $query
     * @param mixed   $user
     * @param string  $property
     *
     * @return void
     */
    public function scopeHasStudent($query, $user, $property = 'id')
    {
        $query->whereHas(
            'students', function ($query) use ($user, $property) {
                $query->select('users.id');

                static::filterUsers($query, $user, $property);
            }
        );
    }

    /**
     * Filter by organisation admins
     *
     * @param Builder $query
     * @param mixed   $user
     * @param string  $property
     *
     * @return void
     */
    public function scopeHasOrganizationAdmin($query, $user, $property = 'id')
    {
        $query->whereHas(
            'organizationAdmins', function ($query) use ($user, $property) {
                $query->select('users.id');
                static::filterUsers($query, $user, $property);
            }
        );
    }

    /**
     * Filter users using the given property and values
     *
     * @param Builder $query
     * @param mixed   $user
     * @param string  $property
     *
     * @return void
     */

    public function scopeFilterUsers($query, $user, $property = 'id')
    {
        static::filterUsers($query, $user, $property);
    }
}