<?php

namespace Nitm\Content\Events;

use Nitm\Content\Models\User;
use Illuminate\Support\Arr;
use Nitm\Content\Traits\SupportsAutomation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

abstract class BaseNotifyActivityEvent extends BaseAutomationEvent
{
    use Dispatchable, InteractsWithSockets, InteractsWithQueue, SerializesModels, SupportsAutomation;

    /**
     * The user
     *
     * @var User
     */
    public $user;

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
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(array $message, array $data = [])
    {
        $this->message = Arr::get($message, 'message', 'teams.new_user_activity');
        $this->messageParams = Arr::get($message, 'params', []);
        $this->data = $data;
        $this->team = $team;
    }

    public static function getVariables()
    {
        return [
            'team.name' => 'The team',
            'message' => 'The message'
        ];
    }

    public function __($message = null)
    {
        return static::prepareMessage($message);
    }
}