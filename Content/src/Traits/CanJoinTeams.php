<?php
namespace Nitm\Content\Traits;

use InvalidArgumentException;
use Nitm\Content\Contracts\TeamContract;
use Nitm\Content\Models\Invitation;
use Nitm\Content\NitmContent;

trait CanJoinTeams {
    /**
     * Determine if the user is a member of any teams.
     *
     * @return bool
     */
    public function hasTeams() {
        return count($this->teams) > 0;
    }

    /**
     * Get all of the teams that the user belongs to.
     */
    public function teams() {
        return $this->belongsToMany(
            NitmContent::teamModel(),
            'team_users',
            'user_id',
            'team_id'
        )->withPivot(['role'])->orderBy('name', 'asc');
    }

    /**
     * Get all of the pending invitations for the user.
     */
    public function invitations() {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Determine if the user is on the given team.
     *
     * @param \Nitm\Content\Models\Team $team
     * @return bool
     */
    public function onTeam($team) {
        return $this->teams->contains($team);
    }

    /**
     * Determine if the given team is owned by the user.
     *
     * @param \Nitm\Content\Models\Team $team
     * @return bool
     */
    public function ownsTeam($team) {
        return $this->id && $team->owner_id && $this->id === $team->owner_id;
    }

    /**
     * Get all of the teams that the user owns.
     */
    public function ownedTeams() {
        return $this->hasMany(NitmContent::teamModel(), 'owner_id');
    }

    /**
     * Get the user's role on the team currently being viewed.
     *
     * @return string
     */
    public function roleOnCurrentTeam() {
        return $this->roleOn($this->currentTeam);
    }

    /**
     * Accessor for the currentTeam method.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getCurrentTeamAttribute() {
        return $this->currentTeam();
    }

    /**
     * Get the team that user is currently viewing.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function currentTeam() {
        if (is_null($this->current_team_id) && $this->hasTeams()) {
            $this->switchToTeam($this->teams->first());

            return $this->currentTeam();
        } elseif (! is_null($this->current_team_id)) {
            $currentTeam = $this->teams->find($this->current_team_id);

            return $currentTeam ?: $this->refreshCurrentTeam();
        }

        return null;
    }

    /**
     * Determine if the current team is on a trial.
     *
     * @return bool
     */
    public function currentTeamOnTrial() {
        return $this->currentTeam() && $this->currentTeam()->onTrial();
    }

    /**
     * Determine if the user owns the current team.
     *
     * @return bool
     */
    public function ownsCurrentTeam() {
        return $this->currentTeam() && $this->currentTeam()->owner_id === $this->id;
    }

    /**
     * Switch the current team for the user.
     *
     * @param \Nitm\Content\Models\Team $team
     * @return void
     */
    public function switchToTeam($team) {
        if (! $this->onTeam($team)) {
            throw new InvalidArgumentException(__("teams.user_doesnt_belong_to_team"));
        }

        $this->current_team_id = $team->id;

        $this->save();
    }

    /**
     * Refresh the current team for the user.
     *
     * @return \Nitm\Content\Models\Team
     */
    public function refreshCurrentTeam() {
        $this->current_team_id = null;

        $this->save();

        return $this->currentTeam();
    }

    /**
     * Get the total number of potential collaborators across all teams.
     *
     * This does not include the current user instance.
     *
     * @return int
     */
    public function totalPotentialCollaborators() {
        return $this->ownedTeams->sum(
            function ($team) {
                return $team->totalPotentialUsers();
            }
        ) - $this->ownedTeams->count();
    }

    /**
     * Get the user's role on a given team.
     * @param TeamContract|null $team The team
     *
     * @return string
     */
    public function roleOn(?TeamContract $team = null): string {
        if (! $team || $team && $this->current_team_id == $team->id) {
            if (! $this->relationLoaded('teamUser')) {
                $this->load('teamUser');
            }
            return $this->teamUser ? $this->teamUser->role : 'pending';
        } else {
            if ($team = $this->teams->find($team->id)) {
                return $team->pivot ? $team->pivot->role : 'pending';
            }
            return 'pending';
        }
    }

    /**
     * Is the user an admin on the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isAdminOn(?TeamContract $team = null): bool {
        return $this->isSuperAdmin() || $this->isOrganizationAdmin($team) || $this->isOwnerOf($team);
    }

    /**
     * Is the user a super admin?
     *
     * @return bool
     */
    public function isSuperAdmin(): bool {
        return $this->hasRole('Super Admin') || $this->systemRole && $this->systemRole->name === 'Super Admin';
    }

    /**
     * Is the user an admin on the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isOrganizationAdmin(?TeamContract $team = null): bool {
        $team = $team ?: request()->team ?: $this->team ?: $this->currentTeam;
        return $team instanceof TeamContract ? $this->roleOn($team) === 'organization-admin' : false;
    }

    /**
     * Is the user on the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isUserOn(?TeamContract $team = null): bool {
        $team = $team ?: request()->team ?: $this->team ?: $this->currentTeam;
        return $team instanceof TeamContract ? $this->roleOn($team) !== null : false;
    }

    /**
     * Is the user approved on the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isPendingOn(?TeamContract $team = null): bool {
        $team = $team ?: request()->team ?: $this->team ?: $this->currentTeam;
        return $team instanceof TeamContract ? in_array($this->roleOn($team), ['pending', 'member']) : false;
    }

    /**
     * Is the user the owner of the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isOwnerOf(?TeamContract $team = null): bool {
        $team = $team ?: request()->team ?: $this->team ?: $this->currentTeam;
        return $team instanceof TeamContract ? $team->owner_id === $this->id : false;
    }

    /**
     * Is the user approved on the given team?
     * @param TeamContract $team The team
     *
     * @return bool
     */
    public function isApprovedOn(?TeamContract $team = null): bool {
        if ((! $team && $this->teamUser) || $this->pivot) {
            if ($this->teamUser) {
                return $this->teamUser ? \Nitm\Helpers\ModelHelper::boolval($this->teamUser->is_approved) === true : false;
            }
            if ($this->pivot) {
                return $this->pivot ? \Nitm\Helpers\ModelHelper::boolval($this->pivot->is_approved) === true : false;
            }
        } else {
            $team = $team ?: request()->team ?: $this->team ?: $this->currentTeam;
            if ($team instanceof TeamContract) {
                if (! $team->pivot) {
                    if ($this->relationLoaded('teams') && $this->teams) {
                        $team = $this->teams->where('id', $team->id)->first();
                    } else {
                        $team = $this->teams()->where('teams.id', $team->id)->first();
                    }
                }
                if ($team instanceof TeamContract) {
                    return $team->pivot ? \Nitm\Helpers\ModelHelper::boolval($team->pivot->is_approved) === true : false;
                }
                return false;
            } else {
                return false;
            }
        }

        return false;
    }
}