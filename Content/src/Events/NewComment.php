<?php
namespace Nitm\Content\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nitm\Content\Models\NotificationPreference;
use Nitm\Content\Models\User;

class NewComment extends BaseAutomationEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Model $comment
     * @return void
     */
    public function __construct(Model $comment) {
        $this->model = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        $ids = array_unique(
            array_merge(
                [],
                $this->model->commentable->comments()->pluck('user_id')->all()
            )
        );

        return User::select('users.id')->whereIn('users.id', $ids)
            ->whereNotIn('users.id', [$this->model->user_id])
            ->whereHas('notificationPreferences', function ($query) {
                $query->enabledFor(static::class)->via(NotificationPreference::VIA_WEB);
            })
            ->get()
            ->unique('id')
            ->map(function ($user) {
                return new PrivateChannel('users.' . $user->id);
            })
            ->all();
    }
}