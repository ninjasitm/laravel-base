<?php

namespace Nitm\Content\Contracts\Repositories;

use Nitm\Content\Announcement;

interface AnnouncementRepository
{
    /**
     * Get the most recent announcement notifications for the application.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recent();

    /**
     * Create an application announcement with the given data.
     *
     * @param  \Illuminate\Contracts\Authenticatable
     * @param  array                                 $data
     * @return \Nitm\Content\Announcement
     */
    public function create($user, array $data);

    /**
     * Update the given announcement with the given data.
     *
     * @param \Nitm\Content\Announcement $announcement
     * @param array                      $data
     */
    public function update(Announcement $announcement, array $data);
}
