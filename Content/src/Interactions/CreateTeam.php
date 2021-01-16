<?php

namespace Nitm\Content\Interactions;

use Nitm\Content\Spark;
use Illuminate\Support\Facades\Validator;
use Nitm\Content\Events\Teams\TeamCreated;
use Nitm\Content\Events\Teams\TeamOwnerAdded;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Nitm\Content\Contracts\Repositories\TeamRepository;
use Nitm\Content\Contracts\Interactions\CreateTeam as Contract;
use Nitm\Content\Contracts\Interactions\AddTeamMember as AddTeamMemberContract;

class CreateTeam implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function validator($user, array $data)
    {
        $validator = Validator::make($data, Spark::call(static::class.'@rules'));

        $validator->sometimes(
            'slug', 'required|alpha_dash|max:255|unique:teams,slug', function () {
                return Spark::teamsIdentifiedByPath();
            }
        );

        $validator->after(
            function ($validator) use ($user) {
                $this->validateMaximumTeamsNotExceeded($validator, $user);
            }
        );

        return $validator;
    }

    /**
     * Validate that the maximum number of teams hasn't been exceeded.
     *
     * @param  \Illuminate\Validation\Validator           $validator
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    protected function validateMaximumTeamsNotExceeded($validator, $user)
    {
        if (! $plan = $user->sparkPlan()) {
            return;
        }

        if (is_null($plan->teams)) {
            return;
        }

        if ($plan->teams <= $user->ownedTeams()->count()) {
            $validator->errors()->add(
                'name', __('teams.please_upgrade_to_create_more_teams')
            );
        }
    }

    /**
     * Get the basic validation rules for creating a new team.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle($user, array $data)
    {
        event(
            new TeamCreated(
                $team = Spark::interact(
                    TeamRepository::class.'@create', [$user, $data]
                )
            )
        );

        Spark::interact(
            AddTeamMemberContract::class, [
            $team, $user, 'owner'
            ]
        );

        event(new TeamOwnerAdded($team, $user));

        try {
            if (Spark::chargesUsersPerTeam() && $user->subscription()
                && $user->ownedTeams()->count() > 1
            ) {
                $user->addSeat();
            }
        } catch (IncompletePayment $e) {
            return [$team, $e->payment->id];
        }

        return [$team, null];
    }
}
