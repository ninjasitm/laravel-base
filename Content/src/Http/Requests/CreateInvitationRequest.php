<?php

namespace Nitm\Content\Http\Requests;

use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CreateInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->ownsTeam($this->team);
    }

    /**
     * Get the validator for the request.
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator()
    {
        $validator = Validator::make(
            $this->all(), [
            'email' => 'required|email|max:255',
            ]
        );

        return $validator->after(
            function ($validator) {
                return $this->verifyEmailNotAlreadyOnTeam($validator, $this->team)
                    ->verifyEmailNotAlreadyInvited($validator, $this->team);
            }
        );
    }


    /**
     * Determine if the request will exceed the max allowed team members.
     *
     * @param  \Nitm\Content\Plan $plan
     * @return bool
     */
    protected function exceedsMaxTeamMembers($plan)
    {
        return ! is_null($plan->teamMembers) &&
               $plan->teamMembers <= $this->team->totalPotentialUsers();
    }

    /**
     * Determine if the request will exceed the max allowed collaborators.
     *
     * @param  \Nitm\Content\Plan $plan
     * @return bool
     */
    protected function exceedsMaxCollaborators($plan)
    {
        return ! is_null($plan->collaborators) &&
               $plan->collaborators <= $this->user()->totalPotentialCollaborators();
    }

    /**
     * Verify that the given e-mail is not already on the team.
     *
     * @param  \Illuminate\Validation\Validator $validator
     * @param  \Nitm\Content\Models\Team        $team
     * @return $this
     */
    protected function verifyEmailNotAlreadyOnTeam($validator, $team)
    {
        if ($team->users()->where('email', $this->email)->exists()) {
            $validator->errors()->add('email', __('teams.user_already_on_team'));
        }

        return $this;
    }

    /**
     * Verify that the given e-mail is not already invited.
     *
     * @param  \Illuminate\Validation\Validator $validator
     * @param  \Nitm\Content\Models\Team        $team
     * @return $this
     */
    protected function verifyEmailNotAlreadyInvited($validator, $team)
    {
        if ($team->invitations()->where('email', $this->email)->exists()) {
            $validator->errors()->add('email', __('teams.user_already_invited_to_team'));
        }

        return $this;
    }
}