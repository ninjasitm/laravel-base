<?php

namespace Nitm\Content\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The payload data
     *
     * @var array
     */
    public $data = [];

    /**
     * The action
     *
     * @var string
     */
    public $action;

    /**
     * The message
     *
     * @var string
     */
    public $message;

    /**
     * The url
     *
     * @var string
     */
    public $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $action = null, string $message = null, string $url = null)
    {
        //
        $this->action = $action;
        $this->message = $message;
        $this->url = $url ?? config('app.webUrl');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [FcmFunctionChannel::class];
    }

    /**
     * The original event that sent this notification
     */
    protected $event;

    public function setData(array $data)
    {
        foreach (['message', 'url', 'action', 'user', 'event', 'data'] as $property) {
            $value = Arr::get($data, $property);
            if (property_exists($this, $property) && $value) {
                $this->$property = $value;
            }
        }
        return $this;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if ($this->message) {
            return $this->message;
        }

        if ($this->event && property_exists($this->event, 'message')) {
            return $this->event->message;
        }
    }

    /**
     * @return string
     */
    public function getAction()
    {
        if ($this->action) {
            return $this->action;
        }

        if ($this->event && property_exists($this->event, 'action')) {
            return $this->event->action;
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = '';
        if ($this->url) {
            $url = $this->url;
        }

        if ($this->event && property_exists($this->event, 'url')) {
            $url = $this->event->url;
        }

        return Str::startsWith($url, ['http', 'www', config('app.webUrl')]) ? $url : config('app.webUrl') . '/' . ltrim($url, '/');
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return new \Illuminate\Notifications\Messages\MailMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable = null)
    {
        return [
            'action' => $this->action,
            'message' => $this->message,
            'url' => $this->getUrl(),
            'data' => $this->data
        ];
    }
}