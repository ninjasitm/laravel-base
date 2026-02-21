<?php

namespace Nitm\Content\Listeners;


class NotifyUserOfActivity extends BaseNotifyOfActivity
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
        if ($event->user) {
            $users = collect([$event->user]);
            return compact('users', 'data');
        }
        return $data;
    }
}