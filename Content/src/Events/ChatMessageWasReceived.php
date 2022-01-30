<?php

namespace Nitm\Content\Events;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Nitm\Content\Models\ChatMessage;
use Nitm\Content\Models\User;

class ChatMessageWasReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user;

    public function __construct(ChatMessage $message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        if (!$this->message->thread->group_id) {
            $toId = $this->message->thread->to_id == $this->user->id ? $this->message->thread->user_id : $this->message->thread->to_id;
            return [
                new PrivateChannel('dm-chat.' . $this->message->thread->thread_id . '.' . $toId),
                new PrivateChannel('chat.' . $toId),
                new PrivateChannel('users.' . $toId)
            ];
        } else {
            $channels = [];

            foreach ($this->message->thread->group->members->whereNotIn('id', [$this->user->id])->all() as $user) {
                array_push($channels, new PrivateChannel('group-chat.' . $this->message->thread->thread_id . '.' . $user->id));
                array_push($channels, new PrivateChannel('chat.' . $user->id));
                array_push($channels, new PrivateChannel('users.' . $user->id));
            }

            return $channels;
        }
    }

    public function broadcastWith()
    {
        //TODO: Adjust format to match format of Laravel Spark Notifications
        return [
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'to_id' => $this->message->to_id,
                'user_id' => $this->message->user_id,
                'to' => $this->message->to,
                'user' => $this->message->user,
                'creator' => $this->message->user,
                'thread_id' => $this->message->thread_id,
                'date' => '1 second ago',
                'created_at' => Carbon::now(),
                'thread' => [
                    'id' => $this->message->thread->id,
                    'thread_id' => $this->message->thread_id,
                    'timestamp' => Carbon::now()->getTimestamp(),
                    'updated_at' => Carbon::now()
                ]
            ]
        ];
    }
}
