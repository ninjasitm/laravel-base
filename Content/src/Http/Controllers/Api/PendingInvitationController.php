<?php

namespace Nitm\Content\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Nitm\Content\Contracts\Interactions\AddTeamMember;
use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Models\Invitation;
use Nitm\Content\NitmContent;

class PendingInvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all of the pending invitations for the user.
     *
     * @return Response
     */
    public function all(Request $request)
    {
        return $request->user()->invitations()->with('team')->get();
    }

    /**
     * Accept the given invitations.
     *
     * @return Response
     */
    public function accept(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->id === $invitation->user_id, 404);

        NitmContent::interact(
            AddTeamMember::class,
            [
                $invitation->team,
                $request->user(),
                $invitation->role,
            ]
        );

        $invitation->delete();
    }

    /**
     * Reject the given invitations.
     *
     * @return Response
     */
    public function reject(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->id === $invitation->user_id, 404);

        $invitation->delete();
    }
}
