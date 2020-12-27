<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spark;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Nitm\Content\Models\Calendar;
use Nitm\Content\Models\CalendarEntry;

trait FormatsDateTimeLegacy
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
        $realStartDate = in_array($startDate->shortEnglishDayOfWeek, $days) ? $startDate : $startDate->parse(strtotime("next " . current($days), $startDate->timestamp));
        foreach ($days as $day) {
            $date = $startDate->parse(strtotime("next $day", $startDate->timestamp));
            if ($date->diffInDays($startDate) < $realStartDate->diffInDays($startDate)) {
                $realStartDate = $date;
            }
        }
        return $realStartDate;
    }

    public function getStartTimeAttribute()
    {
        return Arr::get($this->attributes, 'end_time', null) ? Carbon::parse($this->attributes['start_time'])->format('H:i') : Calendar::DEFAULT_START_TIME;
    }

    public function getEndTimeAttribute()
    {
        return Arr::get($this->attributes, 'end_time', null) ? Carbon::parse($this->attributes['end_time'])->format('H:i') : Calendar::DEFAULT_END_TIME;
    }

    /**
     * Gets the dates between a range
     * @url https://hdtuto.com/article/how-to-get-all-dates-between-two-dates-in-php-carbon-
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return void
     */
    public function generateDateRange(Carbon $startDate, Carbon $endDate)
    {
        $dates = [];

        for ($date = $startDate; $date->lte($enDate); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
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

    protected function getDateParameters(array $requestData = [], bool $excludeStart = false, array $daysOfWeek = [])
    {
        // $entryDates = $this->entries()->selectRaw([
        //     'MAX(end_date) max_end_date',
        //     'MAX(start_date) as max_start_date',
        //     'start_date',
        //     'end_date'
        // ]);

        $daysOfWeek = array_values($daysOfWeek);

        $orderedDays = array_values(static::dayOfWeekOptions());

        $days = array_values(is_array($daysOfWeek) && !empty($daysOfWeek) ? $daysOfWeek : $this->getDaysBetween($this->start_date, $this->end_date, $excludeStart));
        if (empty($days)) {
            return;
        }

        $weeks = $this->end_date->diffInWeeks($this->start_date);
        $weeks = !$weeks && count($days) ? 1 : $weeks;

        // The original event was already added
        $entryStartDate = $this->start_date;

        /**
         * Get the next day intelligently.
         */
        $currentStartDayIndex = array_search($entryStartDate->shortEnglishDayOfWeek, $days);
        $currentStartDay = $days[$currentStartDayIndex];

        $nextDay = Arr::get($days, $currentStartDayIndex + 1, $currentStartDay);

        /**
         * Start at the next day in the list of selected days
         */
        if ($excludeStart) {
            $entryStartDate = Carbon::parse(strtotime("next $nextDay", $this->start_date->timestamp));
            unset($days[$currentStartDayIndex]);
            $days[] = $currentStartDay;
            $currentStartDayIndex = array_search($nextDay, $days);
        }

        /**
         * Make sure we're starting on the right first day
         * If it's the current start date then use that otherwise use the next day
         */
        $firstDay = $nextDay === $entryStartDate->shortEnglishDayOfWeek ? $entryStartDate->shortEnglishDayOfWeek : Carbon::parse(strtotime("next $nextDay", $entryStartDate->timestamp))->shortEnglishDayOfWeek;

        $startDayOfWeek = array_search($firstDay, array_keys($orderedDays));

        /**
         * Make sure we're starting on the right day if the selected days doesn't include today
         * We do this by checking the array index of the current day against the first start day
         */
        if ($entryStartDate->dayOfWeek < $startDayOfWeek) {
            $entryStartDate = Carbon::parse(strtotime("next $firstDay", $entryStartDate->timestamp));
            $startDayOfWeek = $entryStartDate->dayOfWeek;
        }

        $dayValues = array_values($days);

        $days = array_merge(array_splice($dayValues, $startDayOfWeek), $dayValues);

        $startTime = $this->start_time ?? Calendar::DEFAULT_START_TIME;
        $endTime = $this->end_time ?? Calendar::DEFAULT_END_TIME;

        // We need to compensate for dates for a full week
        if (count($days) == 7) {
            $days[] = $days[0];
        }

        return compact("weeks", "days", "startTime", "endTime", "entryStartDate");
    }

    /**
     * Prepare entries for addition to the database
     *
     * @param bool $excludeStart Exclude the start datae from the range?
     * @param int $sequenceId Should the entries be assigned to an entry sequence?
     * @return array
     */
    public function prepareEntries(bool $excludeStart = false, int $sequenceId = null, array $requestData = [], $daysOfWeek = [])
    {
        $array = collect([]);

        $entryParameters = $this->getDateParameters($requestData, $excludeStart, $daysOfWeek ?: []);
        if (is_array($entryParameters) && !empty($entryParameters)) {
            extract($entryParameters);

            if (isset($weeks) && count($days)) {
                for ($i = 0; $i < $weeks; $i++) {
                    $currentWeek = clone $entryStartDate;
                    foreach ($days as $day) {
                        if ($entryStartDate->greaterThan($this->end_date)) {
                            break;
                        }

                        /**
                         * Now create each entry based on the day
                         */
                        $array[] = new CalendarEntry([
                            'date' => $entryStartDate,
                            'day_of_week' => $day,
                            'start_date' => $this->start_date,
                            'end_date' => $this->end_date,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => static::STATUS_ON,
                            'entity_type' => $this->entity_type ?: 'calendar',
                            'entity_id' => $this->entity_id ?: $this->id,
                            'sequence_owner_id' => $sequenceId ?? null
                        ]);
                        $entryStartDate = $entryStartDate->parse(strtotime("next $day", $currentWeek->timestamp));
                    }
                    // $entryStartDate = $currentWeek->addWeeks(1);
                }
            }

            return $array->unique('date');
        }
        return collect([]);
    }
}
