<?php

namespace Nitm\Content\Events;

use Nitm\Content\Models\User;
use Cog\Contracts\Love\Reaction\Models\Reaction;
use Illuminate\Broadcasting\Channel;
use Nitm\Content\Models\NotificationPreference;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Cog\Contracts\Love\Reaction\Models\Reaction as ReactionInterface;

class NewReaction extends BaseAutomationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reaction $reaction)
    {
        $this->model = $reaction;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [];
        $ids = [];
        $reactable = $this->model->reactant->reactable;

        User::select('users.id')->whereIn('users.id', $ids)
            ->whereNotIn('users.id', [$this->model->user_id])
            ->whereHas('notificationPreferences', function ($query) {
                $query->enabledFor(ListenersNewReaction::class)->via(NotificationPreference::VIA_WEB);
            })
            ->get()
            ->unique('users.id')
            ->reduce(function ($carry, $user) use ($channels) {
                array_push($channels, new PrivateChannel('users.' . $user->id));
            });

        return $channels;
    }

    public function getReaction(): ReactionInterface
    {
        return $this->model;
    }
}