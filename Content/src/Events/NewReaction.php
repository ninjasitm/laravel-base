<?php
namespace Nitm\Content\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nitm\Content\Models\NotificationPreference;
use Nitm\Content\Models\User;

class NewReaction extends BaseAutomationEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Model $reaction
     * @return void
     */
    public function __construct(Model $reaction) {
        $this->model = $reaction;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        $reactable = $this->model->reactant->reactable;
        $ids       = collect([
            $reactable->user_id ?? null,
        ])->merge(
            method_exists($reactable, 'comments') ? $reactable->comments()->pluck('user_id')->all() : []
        )
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return [];
        }

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

    public function getReaction(): Model {
        return $this->model;
    }
}