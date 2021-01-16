<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use NitmContent;
use Carbon\Carbon;

trait HasDueOn
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasDueOn()
    {
        $this->fillable = $this->fillable ?: [];
        array_push(
            $this->fillable,
            'due_on',
            'due_in',
            'due_in_unit',
            'due_in_time'
        );

        $this->dates = $this->dates ?: [];
        array_push(
            $this->dates,
            'due_on',
            'public_due_on'
        );
    }

    public function getDueInUnits()
    {
        $units = [
            'day', 'hour', 'minute', 'week', 'year'
        ];
        return array_combine($units, array_map('ucfirst', $units));
    }

    public function setDueInUnitAttribute($value)
    {
        if ($value) {
            $this->attributes['due_in_unit'] = strtolower(intval($this->due_in) === 1 ? Str::singular($value) : Str::plural($value));
        }
    }

    // public function setDueOnAttribute($value)
    // {
    //     if (!empty($value)) {
    //         $value = Carbon::parse($value);
    //         $this->attributes['due_on'] = $value;
    //     } else {
    //         $this->attributes['due_on'] = null;
    //     }
    // }

    // public function setPublicDueOnAttribute($value)
    // {
    //     if (!empty($value)) {
    //         $value = Carbon::parse($value);
    //         $this->attributes['public_due_on'] = $value;
    //     } else {
    //         $this->attributes['public_due_on'] = null;
    //     }
    // }

    /**
     * Check for items expiring in the given interval
     *
     * @param integer $count
     * @param string  $interval
     * @param string  $from
     * @param string  $timezone
     *
     * @return void
     */
    public function scopeExpiringIn($query, $count = 1, $interval = 'hour', $timezone = 'UTC', $from = 'NOW()')
    {
        $timezone = $timezone ?? 'UTC';
        // Select deliverables that are past due
        // Using either the due_on or the interval of the program instance and the due_in + due_in_unitn attribute
        $from = strtolower($from) == 'now()' ? $from : "'$from'";
        return $query->whereRaw(
            "(
                {$this->table}.public_due_on IS NOT NULL
                AND ({$this->table}.public_due_on::timestamp AT TIME ZONE '$timezone')
                BETWEEN ($from AT TIME ZONE '$timezone') AND ((NOW() AT TIME ZONE '$timezone') + interval '$count $interval')
            )
            OR
            (
                {$this->table}.due_on IS NOT NULL
                AND ({$this->table}.due_on::timestamp AT TIME ZONE '$timezone')
                BETWEEN ($from AT TIME ZONE '$timezone') AND ((NOW() AT TIME ZONE '$timezone') + interval '$count $interval')
            )"
        );
    }
}
