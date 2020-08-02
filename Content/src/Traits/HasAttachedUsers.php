<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Models\AttachedUser;
use Nitm\Content\Helpers\AttachedUsersHelper;
use Illuminate\Database\Eloquent\Builder;

trait HasAttachedUsers
{
    // public static function bootAttachedUsers()
    // {
    //     $builder = function ($relation, $type = null) {
    //         ModelHelper::bootRelation(User::class, $relation, [
    //             'id', 'name', 'email'
    //         ], function ($query) use ($type) {
    //             $where = [
    //                 'attached_users.entity_id' => $this->id,
    //                 'attached_users.entity_type' => $this->getMorphClass()
    //             ];
    //             if ($type) {
    //                 $where['attached_users.type'] = $type;
    //             }
    //             $query->on('users.id', '=', 'attached_users.user_id')
    //                 ->where($where);
    //         });
    //     };

    //     $builder('users');
    //     $builder('mentors', 'mentor');
    //     $builder('students', 'student');
    //     $builder('organizationAdmins', 'arganization-admin');
    // }

    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasAttachedUsers()
    {
        $this->addCustomWith(
            [
                'mentors',
                'students'
            ]
        );

        $this->addCustomWithCount(
            [
                'mentors',
                'students'
            ]
        );
    }

    public function attachedUsers(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AttachedUser::class, 'entity');
    }

    /**
     * Get the users for this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return AttachedUsersHelper::getUsersRelation($this);
    }

    /**
     * Get the members for this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function members(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->users();
    }

    public function mentors(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->users()->where('attached_users.type', '=', 'mentor');
    }

    public function mentorsOrStudents(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->mentors()->orWhere('attached_users.type', '=', 'student');
    }

    public function students(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->users()->where('attached_users.type', '=', 'student');
    }

    public function organizationAdmins(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->users()->where('attached_users.type', '=', 'organization-admin');
    }

    public function syncUsers($data, $type = 'user')
    {
        return AttachedUsersHelper::syncUsers($this, $data, $type);
    }

    public function syncMentors($data)
    {
        $this->syncUsers($data, 'mentor');
    }

    public function syncStudents($data)
    {
        $this->syncUsers($data, 'student');
    }

    public function syncOrganizationAdmins($data)
    {
        $this->syncUsers($data, 'organization-admin');
    }
}
