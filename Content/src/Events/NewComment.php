<?php

namespace Nitm\Content\Events;

use Nitm\Content\Models\User;
use Nitm\Content\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Nitm\Content\Models\NotificationPreference;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewComment extends BaseAutomationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Comment $comment)
    {
        $this->model = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        $ids = array_unique(
            array_merge(
                [],
                $this->model->commentable->comments()->pluck('user_id')->all()
            )
        );

        User::select('users.id')->whereIn('users.id', $ids)
            ->whereNotIn('users.id', [$this->model->user_id])
            ->whereHas('notificationPreferences', function ($query) {
                $query->enabledFor(ListenersNewComment::class)->via(NotificationPreference::VIA_WEB);
            })
            ->get()
            ->unique('id')
            ->reduce(function ($carry, $user) use ($channels) {
                array_push($channels, new PrivateChannel('users.' . $user->id));
            });

        return $channels;
    }
}