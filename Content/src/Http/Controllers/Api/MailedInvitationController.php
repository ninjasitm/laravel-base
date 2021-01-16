<?php

namespace Nitm\Content\Http\Controllers\Api;

use Nitm\Content\NitmContent;
use Illuminate\Http\Request;
use Nitm\Content\Models\Invitation;
use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Contracts\Repositories\TeamRepository;
use Nitm\Content\Http\Requests\CreateInvitationRequest;
use Nitm\Content\Contracts\Interactions\SendInvitation;

class MailedInvitationController extends Controller
{
    /**
     * The team repository implementation.
     *
     * @var \Nitm\Content\Contracts\Repositories\TeamRepository
     */
    protected $teams;

    /**
     * Create a new controller instance.
     *
     * @param  \Nitm\Content\Contracts\Repositories\TeamRepository $teams
     * @return void
     */
    public function __construct(TeamRepository $teams)
    {
        $this->teams = $teams;

        $this->middleware('auth');
    }

    /**
     * Get all of the mailed invitations for the given team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Nitm\Content\Models\Team $team
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request, $team)
    {
        abort_unless($request->user()->onTeam($team), 404);

        return $team->invitations;
    }

    /**
     * Create a new invitation.
     *
     * @param  \Nitm\Content\Http\Requests\CreateInvitationRequest $request
     * @param  \Nitm\Content\Models\Team                           $team
     * @return \Illuminate\Http\Response
     */
    public function store(CreateInvitationRequest $request, $team)
    {
        NitmContent::interact(SendInvitation::class, [$team, $request->email, $request->role]);
    }

    /**
     * Cancel / delete the given invitation.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  \Nitm\Content\Models\Invitation $invitation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->ownsTeam($invitation->team), 404);

        $invitation->delete();
    }
}