<?php

namespace Nitm\Content\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Nitm\Content\Contracts\Interactions\SendInvitation;
use Nitm\Content\Contracts\Repositories\TeamRepository;
use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Http\Requests\CreateInvitationRequest;
use Nitm\Content\Models\Invitation;
use Nitm\Content\Models\Team;
use Nitm\Content\NitmContent;

class MailedInvitationController extends Controller
{
    /**
     * The team repository implementation.
     *
     * @var TeamRepository
     */
    protected $teams;

    /**
     * Create a new controller instance.
     *
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
     * @param  Team  $team
     * @return Response
     */
    public function all(Request $request, $team)
    {
        abort_unless($request->user()->onTeam($team), 404);

        return $team->invitations;
    }

    /**
     * Create a new invitation.
     *
     * @param  Team  $team
     * @return Response
     */
    public function store(CreateInvitationRequest $request, $team)
    {
        NitmContent::interact(SendInvitation::class, [$team, $request->email, $request->role]);
    }

    /**
     * Cancel / delete the given invitation.
     *
     * @return Response
     */
    public function destroy(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->ownsTeam($invitation->team), 404);

        $invitation->delete();
    }
}
