<?php
namespace Nitm\Content\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nitm\Content\Models\Notification;

class NotificationCreated {
    use Dispatchable;
    use SerializesModels;

    public Notification $notification;

    public function __construct(Notification $notification) {
        $this->notification = $notification;
    }
}