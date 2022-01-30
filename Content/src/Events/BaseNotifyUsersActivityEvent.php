<?php

namespace Nitm\Content\Events;

use Illuminate\Support\Arr;
use Nitm\Content\Traits\SupportsAutomation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

abstract class BaseNotifyUsersActivityEvent extends BaseAutomationEvent
{
    use Dispatchable, InteractsWithSockets, InteractsWithQueue, SerializesModels, SupportsAutomation;

    /**
     * THe message string of lang key
     *
     * @var string
     */
    public $message;

    /**
     * THe parameters for the message
     *
     * @var array
     */
    public $messageParams = [];

    /**
     * The data to be used fro creating the notification
     *
     * @var array
     */
    public $data = [];

    /**
     * The users instance.
     *
     * @var Collection
     */
    public $users;

    /**
     * The notification to send
     *
     * @var array
     */
    public $notification;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct($users, array $message, array $data = [], $notification = null)
    {
        $this->message = Arr::get($message, 'message', 'users.new_user_activity');
        $this->messageParams = Arr::get($message, 'params', []);
        $this->data = $data;
        $this->users = $users;
        $this->notification = $notification;
    }

    public static function getVariables()
    {
        return [
            'user.*.names' => 'The users',
            'team.name' => 'The team',
            'message' => 'The original message'
        ];
    }

    public function __($message = null)
    {
        return static::prepareMessage($message, ['user' => 'users', 'names' => 'name']);
    }
}