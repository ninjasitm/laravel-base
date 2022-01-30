<?php

namespace Nitm\Content\Events;

use Carbon\Carbon;
use Nitm\Content\Models\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Arr;
use Nitm\Content\Traits\SupportsAutomation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Nitm\Content\Traits\Notifications as NotificationTrait;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FeedNotificationWasReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, InteractsWithQueue, SerializesModels, SupportsAutomation, NotificationTrait;

    public $notification;
    public $channels = [];
    public $user;
    public $users;
    public $data = [];

    public function __construct(array $data, $users, $user = null)
    {
        /**
         * Create this array here because otherwise when unserializing the event we get an error since there is no id for the notification
         * Basically we're using a dummy notification not a saved one to broadcast to users
         */
        $this->notification = $this->newBroadcastNotification($data)->toArray();
        $this->users = is_array($users) ? collect($users)->filter() : $users->filter();
        $this->channels = $this->users->map(
            function ($user) {
                return new PrivateChannel('users.' . ($user instanceof User ? $user->id : intval($user)));
            }
        );
        $this->user = $user ?: auth()->user();
        $this->data = $data;
        return $this;
    }

    public function persistNotification()
    {
        $this->notification->from = auth()->user()->id;
        $this->notification->id = Uuid::uuid4();
        $this->notification->save();
        return $this;
    }

    /**
     * Performance based function for batch inserting many notifications
     *
     * @param  [type] $data
     * @return void
     */
    public function persistManyNotifications($data)
    {
        $notifications = $this->users->map(
            function ($user) use ($data) {
                $data['user_id'] = $user instanceof User ? $user->id : intval($user);
                $data['id'] = Uuid::uuid4()->toString();
                $data['created_at'] = Carbon::now()->toDateTimeString();
                $notification = $this->newBroadcastNotification($data);
                return Arr::only($notification->toArray(), ['id', 'user_id', 'created_by', 'icon', 'body', 'action_text', 'action_url', 'created_at']);
            }
        );
        if ($notifications->count()) {
            \Nitm\Content\Models\Notification::insert($notifications->toArray());
        }
    }

    /**
     * Get ConstructParams
     *
     * @return array
     */
    public function getConstructParams(): array
    {
        return [$this->data, $this->users, $this->user];
    }

    public function broadcastOn()
    {
        if ($this->channels->count()) {
            // return $this->notification->channels;
            return  $this->channels->toArray();
        } else {
            // We're broadcasting on dummy channels
            return [
                new Channel('dummy')
            ];
        }
    }

    public function broadcastWhen()
    {
        return $this->channels->count() > 0;
    }

    public function broadcastWith()
    {
        //TODO: Adjust format to match format of Laravel Spark Notifications
        return [
            'message' => $this->notification
        ];
    }
}