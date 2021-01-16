<?php

namespace Nitm\Content\Events;

class AnnouncementCreated
{
    /**
     * The announcement instance.
     *
     * @var \Nitm\Content\Models\Announcement
     */
    public $announcement;

    /**
     * Create a new announcement instance.
     *
     * @param  \Nitm\Content\Models\Announcement $announcement
     * @return void
     */
    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }
}