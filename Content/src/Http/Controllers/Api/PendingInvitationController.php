<?php

namespace Nitm\Content\Http\Controllers;

use Nitm\Content\Spark;
use Illuminate\Http\Request;
use Nitm\Content\Models\Invitation;
use Nitm\Content\Http\Controllers\Controller;
use Nitm\Content\Contracts\Interactions\AddTeamMember;

class PendingInvitationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all of the pending invitations for the user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return $request->user()->invitations()->with('team')->get();
    }

    /**
     * Accept the given invitations.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  \Nitm\Content\Models\Invitation $invitation
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->id === $invitation->user_id, 404);

        NitmContent::interact(
            AddTeamMember::class, [
            $invitation->team, $request->user(), $invitation->role
            ]
        );

        $invitation->delete();
    }

    /**
     * Reject the given invitations.
     *
     * @param  \Illuminate\Http\Request        $request
     * @param  \Nitm\Content\Models\Invitation $invitation
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, Invitation $invitation)
    {
        abort_unless($request->user()->id === $invitation->user_id, 404);

        $invitation->delete();
    }
}