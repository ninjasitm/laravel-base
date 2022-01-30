<?php

namespace Nitm\Content\Traits\User;

use App\Models\NotificationPreference;

trait HasNotifications
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    // public function initializeHasNotifications()
    // {
    // }

    /**
     * Get notifiation prefrences
     *
     * @return void
     */
    public function notificationPreferences(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificationPreference::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(\Laravel\Spark\Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('read', 0);
    }

    public function announcements()
    {
        return $this->hasMany(\Laravel\Spark\Announcement::class);
    }

    public function unreadAnnouncements()
    {
        return $this->hasMany(\Laravel\Spark\Announcement::class)
            ->join('users', 'users.id', '=', 'announcements.user_id')
            ->whereRaw('announcements.created_at > users.last_read_announcements_at OR announcements.updated_at > users.last_read_announcements_at');
    }
}
