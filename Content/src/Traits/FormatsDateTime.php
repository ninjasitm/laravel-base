<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Nitm\Content\Helpers\DateTimeHelper;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Nitm\Content\Models\Calendar;
use Nitm\Content\Models\CalendarEntry;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;

trait FormatsDateTime
{
    public static function dayOfWeekOptions()
    {
        return [
            'Sun' => 'Sun',
            'Mon' => 'Mon',
            'Tue' => 'Tue',
            'Wed' => 'Wed',
            'Thu' => 'Thu',
            'Fri' => 'Fri',
            'Sat' => 'Sat'
        ];
    }

    public static function repeatFrequencyOptions()
    {
        return [
            CalendarEntry::FREQUENCY_DAILY => Str::title(CalendarEntry::INTERVAL_DAILY),
            CalendarEntry::FREQUENCY_WEEKLY => Str::title(CalendarEntry::INTERVAL_WEEKLY),
            CalendarEntry::FREQUENCY_MONTHLY => Str::title(CalendarEntry::INTERVAL_MONTHLY),
            CalendarEntry::FREQUENCY_YEARLY => Str::title(CalendarEntry::INTERVAL_YEARLY),
        ];
    }

    public static function recurEndsOptions()
    {
        return ['never' => 'never', 'on' => 'on', 'after' => 'after'];
    }

    /**
     * Convert a frequency to an interval
     *
     * @return string
     */
    public static function frequencyToInterval($frequency): string
    {
        $frequency = strtolower($frequency);
        $frequencies = [
            CalendarEntry::FREQUENCY_DAILY => CalendarEntry::INTERVAL_DAILY,
            CalendarEntry::FREQUENCY_WEEKLY => CalendarEntry::INTERVAL_WEEKLY,
            CalendarEntry::FREQUENCY_MONTHLY => CalendarEntry::INTERVAL_MONTHLY,
            CalendarEntry::FREQUENCY_YEARLY => CalendarEntry::INTERVAL_YEARLY,
        ];
        return Arr::get($frequencies, $frequency, CalendarEntry::INTERVAL_DAILY);
    }

    public function setRecurFrequencyAttribute($frequency)
    {
        $this->attributes['recur_frequency'] = $this->ensureFrequencyOption($frequency);
    }

    public function setRecurEndsAttribute($value)
    {
        $this->attributes['recur_ends'] = Arr::get(static::recurEndsOptions(), strtolower($value), 'never');
    }

    public function setRecurEndsCountAttribute($value)
    {
        $value = intval($value);
        $this->attributes['recur_ends_count'] = $value;
        if ($value > 0) {
            $this->attributes['recur_ends'] = 'after';
        }
    }

    public function setRecurEndsOnAttribute($value)
    {
        $this->attributes['recur_ends_on'] = DateTimeHelper::isValidDate($value) ? DateTimeHelper::convertToDateObject($value) : null;
        if ($this->attributes['recur_ends_on']) {
            $this->attributes['recur_ends'] = 'on';
        }
    }

    public function setRecurIntervalAttribute($count)
    {
        $this->attributes['recur_interval'] = $count ?? 1;
        if (intval($count) > 0) {
            $frequency = Arr::get($this->attributes, 'recur_frequency', -1);
            $this->attributes['recur_frequency'] = in_array($frequency, [
                CalendarEntry::FREQUENCY_WEEKLY,
                CalendarEntry::FREQUENCY_MONTHLY,
                CalendarEntry::FREQUENCY_YEARLY
            ]) ? $frequency : CalendarEntry::FREQUENCY_WEEKLY;
        }
    }

    public function setEndTimeAttribute($value)
    {
        $this->prependDateToTime('end', $value);
        $this->appendTimeToDate('end');
    }

    public function setStartTimeAttribute($value)
    {
        $this->prependDateToTime('start', $value);
        $this->appendTimeToDate('start');
    }

    /**
     * Prepend the date to the specified time
     *
     * @param string $qualifier end|start
     * @param string $value The time value
     *
     * @return void
     */
    public function prependDateToTime($qualifier, $value = null)
    {
        $value = $value ?? Arr::get($this->attributes, $qualifier . '_time');
        $date = Arr::get($this->attributes, $qualifier . '_date');
        if ($value) {
            $time = $this->convertToDateObject($value);
            if ($date) {
                $time->setDateFrom(Carbon::parse($date)->format('Y-m-d'));
            }
            $this->attributes[$qualifier . '_time'] = $time ? $time->format('Y-m-d H:i:s') : null;
        }
    }

    /**
     * Prepend the time to the specified date
     *
     * @param string $qualifier end|start
     * @param string $value The date
     *
     * @return void
     */
    public function appendTimeToDate($qualifier, $value = null)
    {
        $value = $value ?? Arr::get($this->attributes, $qualifier . '_date');
        $time = Arr::get($this->attributes, $qualifier . '_time');
        if ($value) {
            $date = $this->convertToDateObject($value);
            if ($time) {
                $date->setTimeFrom(Carbon::parse($time)->format('H:i:s'));
            }
            $this->attributes[$qualifier . '_date'] = $date ? $date->format('Y-m-d H:i:s') : null;
        }
    }

    public function setStartDateAttribute($value)
    {
        if ($value) {
            $this->appendTimeToDate('start', $value);
        } else {
            $this->attributes['start_date'] = null;
        }
    }

    public function setEndDateAttribute($value)
    {
        if ($value) {
            $this->appendTimeToDate('end', $value);
        } else {
            $this->attributes['end_date'] = null;
        }
    }

    // public function getEndTimeAttribute()
    // {
    //     $value = Arr::get($this->attributes, 'end_time');
    //     return $this->timeToCustomTimezone('end_time', $value)->setDateFrom($this->end_date);
    // }

    // public function getStartTimeAttribute()
    // {
    //     $value = Arr::get($this->attributes, 'start_time');
    //     return $this->timeToCustomTimezone('start_time', $value)->setDateFrom($this->start_date);
    // }

    // public function getStartDateAttribute()
    // {
    //     $value = Arr::get($this->attributes, 'start_date');
    //     return $value ? $this->toCustomTimezone('start_date', $value)->setTimeFromTimeString($this->attributes['start_time']) : null;
    // }

    // public function getEndDateAttribute()
    // {
    //     $value = Arr::get($this->attributes, 'end_date');
    //     return $value ? $this->toCustomTimezone('end_date', $value)->setTimeFromTimeString($this->attributes['end_time']) : null;
    // }

    /**
     * Ensure a valid frequency has been set
     *
     * @param string $frequency
     *
     * @return string
     */
    protected function ensureFrequencyOption($frequency): string
    {
        $options = static::repeatFrequencyOptions();
        $frequency = strtolower($frequency);
        return Arr::get($frequency, $options, 'weekly');
    }

    /**
     * Determine the real start date for a model
     *
     * @param CarbonValue $startDate
     * @param array $days
     * @return CarbonValue
     */
    public static function getRealStartDate($startDate, array $days)
    {
        $days = array_values($days);
        $realStartDate = in_array($startDate->shortEnglishDayOfWeek, $days) ? $startDate->clone() : $startDate->parse(strtotime("next " . current($days), $startDate->timestamp))->clone();
        foreach ($days as $day) {
            $date = $startDate->parse(strtotime("next $day", $startDate->timestamp));
            if ($date->diffInDays($startDate) < $realStartDate->diffInDays($startDate)) {
                $realStartDate = $date;
            }
        }

        $realStartDate->setTimeFrom($startDate);
        return $realStartDate;
    }

    /**
     * Get the days between datess, up to 7 days
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param boolean $excludeStart
     * @return void
     */
    public function getDaysBetween(Carbon $startDate, Carbon $endDate, $excludeStart = false)
    {
        $diff = $endDate->diffInDays($startDate);
        if ($diff > 7) {
            $endDate = $startDate->clone();
            $endDate->addDays(6);
        }

        $period = CarbonPeriod::create($startDate, $endDate);
        if ($excludeStart) {
            $period->setStartDate($startDate->addDays(1));
        }

        $dayNumbers = [];
        foreach ($period as $day) {
            $dayNumbers[] = $day->dayOfWeek;
        }

        $days = array_intersect_key(array_values(static::dayOfWeekOptions()), $dayNumbers);

        return array_combine($days, $days);
    }

    /**
     * Get the date rule for this model
     *
     * @param array $requestData
     * @param array $daysOfWeek
     *
     * @return \Recurr\Rule
     */
    protected function getRRule(bool $excludeStartDate = false, array $requestData, array $daysOfWeek = []): Rule
    {
        $rule = new Rule();

        $startDate = $excludeStartDate ? $this->start_date->add(1, 'day') : $this->start_date;
        $rule->setStartDate($startDate, true)
            ->setFreq(strtoupper(CalendarEntry::FREQUENCY_WEEKLY))
            ->setTimezone(config('app.timezone'));

        if ($this->is_recurring) {
            $daysOfWeek = empty($daysOfWeek) ? static::dayOfWeekOptions() : $daysOfWeek;
            $days = array_values($daysOfWeek);

            $rule->setByDay(
                collect($days)->transform(
                    function ($d) {
                        return strtoupper(substr($d, 0, 2));
                    }
                )->all()
            );

            $interval = intval(Arr::get($requestData, 'recur_interval'), $this->recur_interval ?? 1);

            $frequency = $this->ensureFrequencyOption(Arr::get($requestData, 'recur_frequency', CalendarEntry::FREQUENCY_WEEKLY));

            if ($interval > 0) {
                $rule->setInterval($interval)
                    ->setFreq(strtoupper($frequency));
            }

            switch ($this->recur_ends) {
                case 'on':
                    if ($this->recur_ends_on) {
                        $rule->setUntil($this->recur_ends_on)->setEndDate($this->recur_ends_on);
                    }
                    break;

                case 'after':
                    $rule->setCount($this->recur_ends_count);
                    break;

                default:
                    $rule->setUntil(now()->addYears(10))->setEndDate(now()->addYears(10));
                    break;
            }
        } elseif ($this->end_date) {
            $rule->setUntil($this->end_date)
                ->setEndDate($this->end_date);
        } else {
            $rule->setUntil($startDate)
                ->setEndDate($startDate);
        }

        // else {
        // Want to make sure we allow the ability for at least one event to be created
        // $this->end_date = $this->start_date->add(1, static::frequencyToInterval($frequency));
        // $rule->setUntil($this->end_date);
        // }

        // print_r("End date.".$this->end_date);

        return $rule;
    }

    /**
     * Prepare entries for addition to the database
     *
     * @param bool $excludeStart Exclude the start datae from the range?
     * @param int $sequenceId Should the entries be assigned to an entry sequence?
     * @param array $requestData
     * @param array $daysOfWeek
     * @return array
     */
    public function prepareEntries(bool $excludeStart = false, int $sequenceId = null, array $requestData = [], $daysOfWeek = [])
    {
        $array = collect([]);
        if (!$this->is_recurring) {
            return $array;
        }

        $requestData = $requestData ?? $this->attributes;

        $count = intval(Arr::get($requestData, 'recur_ends_count'), $this->recur_ends_count ?? 0);

        if ($count > 0) {
            $this->end_date = null;
            // $count = $excludeStart ? $count - 1 : $count;
            // $count = $count < 1 ? 1 : $count;
        }

        if (!$this->recur_ends) {
            if ($this->recur_ends_on) {
                $this->recur_ends = 'on';
            } elseif ($count > 0) {
                $this->recur_ends = 'after';
                $this->recur_ends_count = $count;
            } else {
                $this->recur_ends = 'never';
            }
        }

        $frequency = $this->ensureFrequencyOption(Arr::get($requestData, 'recur_frequency', 'weekly'));
        $this->recur_frequency = $frequency;


        $rule = $this->getRRule($excludeStart, $requestData, $daysOfWeek ?? static::dayOfWeekOptions());
        // echo "RRule is ".$rule->getString()."\n";
        $entries = (new ArrayTransformer)->transform($rule);
        // echo "Creating ".count($entries)." entries\n";

        if ($entries->count()) {
            $startDate = $this->start_date->equalTo(Carbon::parse($entries->get(0)->getStart())) ? Carbon::parse($entries->remove(0)->getStart()) : $this->start_date;
            $this->start_date = $startDate;
            foreach ($entries as $entry) {
                /**
                 * Now create each entry based on the day
                 */
                $startTime = $this->start_time ?? Calendar::DEFAULT_START_TIME;
                $endTime = $this->end_time ?? Calendar::DEFAULT_END_TIME;
                $array[] = new CalendarEntry(
                    array_merge(
                        Arr::only(
                            $this->attributes,
                            [
                                'entity_type',
                                'entity_id',
                                'recur_frequency',
                                'recur_ends',
                                'recur_ends_on',
                                'recur_ends_after',
                                'rsvp_mode',
                                'rsvp_is_open',
                                'rsvp_is_limited_to_single',
                                'rsvp_limit',
                                'is_all_day'
                            ]
                        ),
                        [
                            'day_of_week' => $entry->getStart()->format('D'),
                            'days_of_week' => $daysOfWeek,
                            'date' => $entry->getStart()->format('Y-m-d H:i:s'),
                            'start_date' => $entry->getStart()->format('Y-m-d H:i:s'),
                            'end_date' => $entry->getEnd()->format('Y-m-d H:i:s'),
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => Calendar::STATUS_ON,
                            'sequence_owner_id' => $sequenceId ?? null,
                            'recur_interval' => 0,
                            'is_all_day' => Arr::get($requestData, 'is_all_day', false),
                            'year_month_week_day' => $entry->getStart()->format("Y-M-W-D")
                        ]
                    )
                );
            }

            // If the recur count was specified make sure to set the correct end date
            // if (!empty($this->recur_interval) && $array->count()) {
            //     $this->end_date = $array->last()->end_date;
            // }

            return $array->unique('date');
        }
        return collect([]);
    }

    /**
     * Get the exected number of entries for this event
     *
     * @return int
     */
    public function getExpectedEntryCount(): int
    {
        // print_R($this->attributes);
        $rule = $this->getRRule(true, $this->attributes, $this->days_of_week ?: static::dayOfWeekOptions());
        // echo "Expected RRule is {$rule->getString()}\n";
        return (new ArrayTransformer)->transform($rule)->count();
    }
}
