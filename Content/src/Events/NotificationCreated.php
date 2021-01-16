<?php

namespace Nitm\Content\Events;

class NotificationCreated
{
    /**
     * The notification instance.
     *
     * @var \Nitm\Content\Models\Notification
     */
    public $notification;

    /**
     * Create a new notification instance.
     *
     * @param  \Nitm\Content\Models\Notification $notification
     * @return void
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }
}