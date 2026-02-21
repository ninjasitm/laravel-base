<?php

namespace Nitm\Content\Listeners;

class NotifyUsersOfActivity extends BaseNotifyOfActivity
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function prepare($event)
    {
        $data = [];
        if ($event->users->count()) {
            $users = $event->users;
            return compact('users', 'data');
        }
        return $data;
    }
}
