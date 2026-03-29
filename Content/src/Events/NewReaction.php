<?php

namespace Nitm\Content\Events;

use Illuminate\Database\Eloquent\Model;
use Nitm\Content\Models\User;
use Nitm\Content\Models\NotificationPreference;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewReaction extends BaseAutomationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Model $reaction
     * @return void
     */
    public function __construct(Model $reaction)
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
                $query->enabledFor(static::class)->via(NotificationPreference::VIA_WEB);
            })
            ->get()
            ->unique('users.id')
            ->reduce(function ($carry, $user) use ($channels) {
                array_push($channels, new PrivateChannel('users.' . $user->id));
            });

        return $channels;
    }

    public function getReaction(): Model
    {
        return $this->model;
    }
}