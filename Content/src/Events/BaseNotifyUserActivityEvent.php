<?php

namespace Nitm\Content\Events;

use Nitm\Content\Models\User;
use Illuminate\Support\Arr;
use Nitm\Content\Traits\SupportsAutomation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

abstract class BaseNotifyUserActivityEvent extends BaseAutomationEvent
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
     * The user instance.
     *
     * @var User
     */
    public $user;

    public $mailable;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(User $user, array $message, array $data = [], $mailable = null)
    {
        $this->message = Arr::get($message, 'message', 'user.new_user_activity');
        $this->messageParams = Arr::get($message, 'params', []);
        $this->data = $data;
        $this->user = $user;
        $this->mailable = null;
    }

    public static function getVariables()
    {
        return [
            'user.name' => 'The user',
            'team.name' => 'The team',
            'message' => 'The message'
        ];
    }

    public function __($message = null)
    {
        return static::prepareMessage($message);
    }
}