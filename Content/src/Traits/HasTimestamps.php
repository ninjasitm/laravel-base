<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Expression;

trait HasTimestamps
{
    /**
     * Find the results where the given date field is within the given timeline
     */
    public function scopeDateWithin($query, string $field, int $count = null, string $interval = null, string $timezone = null)
    {
        $table = $this->getTable();
        if (!is_null($count) && !is_null($interval)) {
            $count = intval($count) ?? 1;
            $interval = $this->sanitizeInterval($interval);
            $timezone = $this->sanitizeTimezone($timezone);
            $query->whereRaw("(
                {$table}.{$field} IS NOT NULL AND {$table}.{$field}::date AT TIME ZONE {$timezone} <= NOW() AT TIME ZONE {$timezone}
                AND {$table}.{$field}::date AT TIME ZONE {$timezone} >= NOW() AT TIME ZONE {$timezone} - INTERVAL '$count $interval'
            )");
        }
    }

    /**
     * Find the results that were created within the given timeline
     */
    public function scopeCreatedWithin($query, int $count = null, string $interval = null, string $timezone = null)
    {
        $query->dateWithin('created_at', $count, $interval, $timezone);
    }

    /**
     * Find the results that were updated within the given timeline
     */
    public function scopeUpdatedWithin($query, int $count = null, string $interval = null, string $timezone = null)
    {
        $query->dateWithin('updated_at', $count, $interval, $timezone);
    }

    /**
     * Find the results that were deleted within the given timeline
     */
    public function scopeDeletedWithin($query, int $count = null, string $interval = null, string $timezone = null)
    {
        $query->dateWithin('deleted_at', $count, $interval, $timezone);
    }

    /**
     * Find the results that were after the given start
     */
    public function scopeDateAfter($query, string $field, \DateTime $start, string $timezone = null)
    {
        $table = $this->getTable();
        $timezone = $this->sanitizeTimezone($timezone);
        $query->whereRaw("({$table}.{$field} IS NOT NULL AND {$table}.{$field}::date AT TIME ZONE {$timezone} > '$start'::date AT TIME ZONE {$timezone})");
    }

    /**
     * Find the results that were created after
     */
    public function scopeCreatedAfter($query, \DateTime $start, string $timezone = null)
    {
        $query->dateAfter('created_at', $start, $timezone);
    }

    /**
     * Find the results that were updated after
     */
    public function scopeUpdatedAfter($query, \DateTime $start, string $timezone = null)
    {
        $query->dateAfter('updated_at', $start, $timezone);
    }

    /**
     * Find the results that were deleted after
     */
    public function scopeDeletedAfter($query, \DateTime $start, string $timezone = null)
    {
        $query->dateAfter('deleted_at', $start, $timezone);
    }

    /**
     * Find the results that were before the given start
     */
    public function scopeDateBefore($query, string $field, \DateTime $start, string $timezone = null)
    {
        $table = $this->getTable();
        $timezone = $timezone ?? config('app.timezone');
        $query->whereRaw("({$table}.{$field} IS NOT NULL AND {$table}.{$field}::date AT TIME ZONE {$timezone} < '$start'::date AT TIME ZONE {$timezone})");
    }

    /**
     * Find the results that were created before
     */
    public function scopeCreatedBefore($query, \DateTime $start, string $timezone = null)
    {
        $query->dateBefore('created_at', $start, $timezone);
    }

    /**
     * Find the results that were updated before
     */
    public function scopeUpdatedBefore($query, \DateTime $start, string $timezone = null)
    {
        $query->dateBefore('updated_at', $start, $timezone);
    }

    /**
     * Find the results that were deleted before
     */
    public function scopeDeletedBefore($query, \DateTime $start, string $timezone = null)
    {
        $query->dateBefore('deleted_at', $start, $timezone);
    }

    public function sanitizeInterval($interval)
    {
        return \Nitm\Content\Helpers\DateTimeHelper::isInterval($interval) ? $interval : 'day';
    }

    /**
     * Sanitize the timezone for the query
     *
     * @param DateTime|string $timezone
     *
     * @return void
     */
    public function sanitizeTimezone($timezone = null)
    {
        $timezone = $timezone ?? config('app.timezone');
        return $timezone instanceof Expression ? $timezone : "'$timezone'";
    }
}
