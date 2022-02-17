<?php

namespace Nitm\Content\Models;

use Nitm\Content\Traits\Search;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Traits\Model as AppModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamUser extends Model
{
    use Search, AppModel;

    public $timestamps = false;
    //
    protected $table = 'team_user';

    protected $with = ['team'];

    protected $fillable = ['is_approved', 'role', 'team_id', 'user_id'];

    protected $visible = ['is_approved', 'role', 'role_name', 'team_id', 'user_id'];

    protected $appends = ['role_name'];

    /**
     * Tteam
     *
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(RelatedTeam::class, 'team_id');
    }

    /**
     * Get Role Name Attribute
     *
     * @return void
     */
    public function getRoleNameAttribute()
    {
        $name = $this->role;
        !$this->relationLoaded('team') || !$this->team ? $this->load('team') : false;
        switch ($this->role) {
            case 'student':
                $name = $this->team->student_role_name;
                break;

            case 'mentor':
                $name = $this->team->user_role_name;
                break;
        }

        return Str::title(str_replace(['_', '-'], ' ', $name));
    }

    /**
     * Set Role Attribute
     *
     * @param  mixed $role
     * @return void
     */
    public function setRoleAttribute($role)
    {
        $team = $this->team ?? request()->route('team');
        $role = is_array($role) ? Arr::get($role, 'id', null) : $role;
        if (!($team instanceof Team)) {
            $teamId = Arr::get($this->attributes, 'team_id', $team instanceof Team ? $team->id : null);
            $team = $team instanceof Team ? $team : Team::find($teamId);
        }
        if ($team instanceof Team) {
            $this->attributes['role'] = $team->resolveRole($role);
        } else {
            $this->attributes['role'] = 'student';
        }
    }
}