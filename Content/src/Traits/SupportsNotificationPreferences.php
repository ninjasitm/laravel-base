<?php

namespace Nitm\Content\Traits;

use Carbon\Carbon;
use App\Helpers\CollectionHelper;
use App\Models\NotificationPreference;

trait SupportsNotificationPreferences
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    // public function initializeSupportsNotificationPreferences()
    // {
    // }

    public function notificationPreferencesForUser()
    {
        $id = auth()->check() ? auth()->user()->id : -1;
        return $this->notificationPreferences()->whereUserId($id);
    }

    /**
     * Are notifications Active?
     *
     * @return bool
     */
    public function notificationsAreActive(): bool
    {
        $now = Carbon::now($this->timezone);
        $hasSilence = false;
        $start = null;
        $end = null;
        $endTime = null;
        $startTime = null;
        if ($this->notifications_silent_from && $this->notifications_silent_to) {
            $startTime = $this->notifications_silent_from ? explode(':', $this->notifications_silent_from) : null;
            $start = !empty($startTime) ? $now->clone()->setTime($startTime[0], $startTime[1]) : null;
            $endTime = $this->notifications_silent_to ? explode(':', $this->notifications_silent_to) : null;
            $end = !empty($endTime) ? $now->clone()->setTime($endTime[0], $endTime[1]) : null;
            // If the end is before the start then most likely this is an overnight silence, so add 1 day
            if ($start && $end && $end->isBefore($start)) {
                $end->addDay();
            }
            $hasSilence = !empty($startTime) && !empty($endTime) && !empty($start) && !empty($end);
        }
        return !$hasSilence || ($hasSilence && !$now->between($start, $end));
    }

    /**
     * Is there an Enabled Notification Preference Via the given class and method?
     *
     * @param  string $class
     * @param  string $via
     * @return bool
     */
    public function hasEnabledNotificationPreferenceVia(string $class, string $via = 'web'): bool
    {
        return !$this->notificationPreferences->count() || ($this->notificationPreferences->count() && $this->notificationPreferences()->whereHas('type', function ($query) use ($class) {
            $query->where('notification_class', $class);
        })->where("via_{$via}", true)->exists());
    }

    /**
     * Enabled
     *
     * @param  mixed $query
     * @return void
     */
    public function scopeEnabled($query)
    {
        $query->whereIsEnabled(true);
    }

    /**
     * scopeHasPreferencesFor
     *
     * @param  mixed $query
     * @param  mixed $classes
     * @return void
     */
    public function scopePreferencesFor($query, $classes)
    {
        $classes = CollectionHelper::isCollection($classes) ? $classes : collect((array) $classes);
        $query->whereHas('type', function ($query) use ($classes) {
            $query->whereIn('notification_class', $classes);
        });
    }

    /**
     * scopeEnabledsFor
     *
     * @param  mixed $query
     * @param  mixed $class
     * @return void
     */
    public function scopeEnabledFor($query, $class)
    {
        $query->preferencesFor($class)->enabled();
    }

    /**
     * Via
     *
     * @param  mixed $query
     * @param  mixed $via
     * @return void
     */
    public function scopeVia($query, $via)
    {
        $via = CollectionHelper::isCollection($via) ? $via : collect((array) $via);
        $via->transform(function ($v) {
            return "via_" . ltrim(strtolower($v), 'via_');
        });
        $query->where($via->combine(array_fill(0, $via->count(), true))->all());
    }
}
