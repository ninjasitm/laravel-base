<?php

namespace Nitm\Helpers;

use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Expression;

/*
 * This class provides some helpful date and time functions
 * @author malcolm@ninjasitm.com
 */

class DateTimeHelper
{
    /**
     * Get the timezones
     *
     * @return array
     */
    public static function getTimezones(): array
    {
        $timezones = collect(DateTimeZone::listIdentifiers(DateTimeZone::AMERICA));
        return $timezones->transform(function ($zone) {
            $date = new Carbon(now(), $zone);
            return [
                'text' => $zone,
                'value' => $zone,
                'short' => $date->format('T'),
                'short_offset' => $date->format('T') . " (" . $date->format('P') . ")",
                'offset' => $date->getOffset()
            ];
        })->all();
    }

    /**
     * Get the time options
     *
     * @return array
     */
    public static function getTimeOptions(): array
    {
        return [
            [
                "value" => "HH:mm",
                "text" => "24HR 00:03"
            ],
            [
                "value" => "LT",
                "text" => "12HR 12:03 AM"
            ]
        ];
    }

    /**
     * Get the date options
     *
     * @return array
     */
    public static function getDateOptions(): array
    {
        return [
            [
                "value" => "ll",
                "text" => "Aug 2, 1985"
            ],
            [
                "value" => "LL",
                "text" => "August 2, 1985"
            ],
            [
                "value" => "MMM-D-YYYY",
                "text" => "Aug-2-1985"
            ],
            [
                "value" => "MMMM-D-YYYY",
                "text" => "August-2-1985"
            ],
            [
                "value" => "MMM D YYYY",
                "text" => "Aug 2 1985"
            ],
            [
                "value" => "MMMM D YYYY",
                "text" => "August 2 1985"
            ],
            [
                "value" => "L",
                "text" => "08/02/1985"
            ],
            [
                "value" => "l",
                "text" => "8/2/1985"
            ]
        ];
    }

    /**
     * Converts a value to a Carbon date object if needed.
     *
     * @param $value
     * @return Carbon
     */
    public static function convertToDateObject($value, $timezone = null)
    {
        if (!$value) {
            return null;
        }

        if (is_object($value) && $value instanceof Carbon) {
            return $value;
        }

        if (is_object($value) && $value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_numeric($value) && is_integer($value)) {
            /** TODO: 2038 Unix Timestamp BUG!!! */
            $value = $value > 2147483647 ? $value / 1000 : $value;
            return Carbon::createFromTimestamp($value, $timezone);
        }

        if (is_string($value) && static::isTimeFormat($value)) {
            return new Carbon($value, $timezone);
        }

        if (is_string($value)) {
            return new Carbon($value, $timezone);
        }

        throw new \Exception('Unable to convert value (' . json_encode($value) . ') to Carbon date object.');
    }

    /**
     * Check to see if the given vale is a valid datetime format
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isDateTimeFormat($value): bool
    {
        return is_string($value) ? preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $value) : false;
    }

    /**
     * Check to see if the given value is in a valid time format
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isTimeFormat($value): bool
    {
        return is_string($value) ? preg_match('/^(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $value) : false;
    }

    /**
     * Check to see if the given date is value
     *
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    public static function isValidDate($date, $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    /**
     * Is this string value a supported interval?
     *
     * @return boolean
     */
    public static function isInterval($interval): bool
    {
        return in_array(strtolower(Str::singular($interval)), [
            'hour',
            'day',
            'week',
            'month',
            'year'
        ]);
    }

    /**
     * @param string $interval
     *
     * @return void
     */
    public static function sanitizeInterval(string $interval)
    {
        return static::isInterval($interval) ? $interval : 'day';
    }

    /**
     * Sanitize the timezone for the query
     *
     * @param Expression|string $timezone
     *
     * @return void
     */
    public static function sanitizeTimezone($timezone = null)
    {
        $timezone = $timezone ?? config('app.timezone');
        return $timezone instanceof Expression ? $timezone : "'$timezone'";
    }
}
