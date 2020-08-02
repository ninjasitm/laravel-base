<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nitm\Content\Helpers\DateTimeHelper;
use Nitm\Content\Team;
use Illuminate\Foundation\Auth\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait DatesTimezoneConversion
{
    protected $times = [];

    /**
     * Dates based on the user timezone
     */
    protected $userTimezoneDates = [];

    /**
     * Dates based on the user timezone
     */
    protected $userTimezoneTimes = [];

    protected function addDateAttributesToArray(array $attributes): array
    {
        $attributes = parent::addDateAttributesToArray($attributes);
        foreach ($attributes as $key => $value) {
            $value = $this->getAttributeValue($key);
            if ($value && $this->isDateObject($key, $value)) {
                $value = $value->format("Y-m-d H:i:s");
            }
            $attributes[$key] = $value;
        }
        return $attributes;
    }

    /**
     * Convert a date value to UTC if it is a date or time
     * @param string $key the attribute
     * @param mixed $value The value being checked
     * @param mixed $timezoneFromUser Used to force a timezone on the given value
     * @return mixed
     */
    public function toUTCTimezone($key, $value, $timezoneFromUser = null)
    {
        // Set UTC timezoned dates
        if ($value instanceof Carbon && $value->getTimezone() === config('app.timezone')) {
            return $value;
        }
        /** @var Nitm\Content\User $user */
        $user = auth()->check() ? auth()->user() : null;

        // if (in_array($key, $this->userTimezoneDates) || $timezoneFromUser === true) {
        if ($user instanceof User && $value) {
            if ($user->timezone) {
                // echo class_basename(get_class($this)).": Trying to set user time for $key to [$value]: Is Carbon Object? (".($value instanceof Carbon).")\n";
                $value = $this->convertToDateObject($value, $value instanceof Carbon ? $user->timezone : config('app.timezone'));
                // $original = $value->clone();
                if ($value instanceof Carbon && $value->getTimezone() != config('app.timezone')) {
                    $value->setTimezone(config('app.timezone'));
                    // echo class_basename(get_class($this)).": Set user time for $key to [$original, $value] from {$original->getTimezone()} to ".config('app.timezone')."\n";
                    // debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                }
            } else {
                /** @var Nitm\Content\Team $team */
                $team = request()->route('team') ?? ($user instanceof User ? ($user->team ?? $user->currentTeam) : null);

                if ($team instanceof Team && $value) {
                    // echo class_basename(get_class($this)).": Setting time for $key with $value from {$team->timezone} to {$value->getTimezone()}\n";
                    $value = $this->convertToDateObject($value, $value instanceof Carbon ? $team->timezone : config('app.timezone'));
                    // $original = $value->clone();
                    if ($value instanceof Carbon && $value->getTimezone() != config('app.timezone')) {
                        $value->setTimezone(config('app.timezone'));
                        // echo class_basename(get_class($this)).": Set time for $key to [$original, $value] from {$original->getTimezone()} to ".config('app.timezone')."\n";
                        // debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                    }
                } elseif ($value) {
                    $value = $this->convertToDateObject($value, config('app.timezone'));
                }
            }
        } elseif ($value) {
            $value = $this->convertToDateObject($value, config('app.timezone'));
        }
        // } elseif (in_array($key, $this->getDates()) || $timezoneFromUser === false) {
        //     /** @var Nitm\Content\Team $team */
        //     $team = request()->route('team') ?? ($user instanceof User ? ($user->team ?? $user->currentTeam) : null);

        //     if ($team instanceof Team && $value) {
        //         // echo class_basename(get_class($this)).": Setting time for $key with $value from {$team->timezone} to {$value->getTimezone()}\n";
        //         $value = $this->convertToDateObject($value, $team->timezone);
        //         if ($value instanceof Carbon && $value->getTimezone() != config('app.timezone')) {
        //             $value->setTimezone(config('app.timezone'));
        //             // echo class_basename(get_class($this)).": Set time for $key to [$original, $value] from {$original->getTimezone()} to ".config('app.timezone')."\n";
        //             // debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        //         }
        //     } elseif ($value) {
        //         $value = Carbon::parse($value, config('app.timezone'));
        //     }
        // }
        return $value;
    }

    public function timeToUTCTimezone($key, $value)
    {
        // Set user timezoned dates
        if ($value && in_array($key, $this->getAllTimes())) {
            return $this->toUTCTimezone($key, $value, (bool)in_array($key, $this->userTimezoneTimes));
        }
        return $value;
    }

    public function toUTCTimezoneIfPossible($key, $value)
    {
        if ($value && in_array($key, $this->getAllDates())) {
            $value = $this->toUTCTimezone($key, $value);
        }
        if ($value && in_array($key, $this->getAllTimes())) {
            $value = $this->timeToUTCTimezone($key, $value);
        }
        return $value;
    }

    /**
     * Convert a date value to a custom if it is a date or time
     * @param string $key the attribute
     * @param mixed $value The value being checked
     * @param mixed $timezoneFromUser Used to force a timezone on the given value
     * @return mixed
     */
    public function toCustomTimezone($key, $value, $timezoneFromUser = null)
    {
        // echo "Converting $key = {$value} on ".get_class($this)."\n";
        /** @var Nitm\Content\User $user */
        /**
         * auth()->check() doesn't do a database call but does identify if the user is logged in or not
         */
        $user = auth()->check() ? auth()->user() : null;

        // if (in_array($key, $this->userTimezoneDates) || $timezoneFromUser === true) {
        $value = $this->convertToDateObject($value);

        if ($value instanceof Carbon && $user instanceof User && $user->timezone && $value->getTimezone() != $user->timezone) {
            $value->setTimezone($user->timezone);
        } elseif (($user instanceof User) || ($user instanceof User && !$user->timezone)) {
            //Fallback to the user's team timezone only if their timezone is empty or there is no user
            /** @var Nitm\Content\Team $team */
            $team = request()->route('team') ?? ($user ? ($user->team ?? $user->currentTeam) : null);

            if ($team instanceof Team && $team->timezone && $value->getTimezone() != $team->timezone) {
                $value->setTimezone($team->timezone);
            }
        }
        // } elseif (in_array($key, $this->getDates()) || $timezoneFromUser === false) {
        //     $value = $this->convertToDateObject($value);
        //     /** @var Nitm\Content\Team $team */
        //     $team = request()->route('team') ?? ($user instanceof User ? ($user->team ?? $user->currentTeam) : null);

        //     if ($value instanceof Carbon && $team instanceof Team && $team->timezone && $value->getTimezone() != $team->timezone) {
        //         $value->setTimezone($team->timezone);
        //     }
        // }

        // echo "\tConverted $key to $value\n";

        return $value;
    }

    public function timeToCustomTimezone($key, $value)
    {
        // Set user timezoned dates
        if ($value && in_array($key, $this->getAllTimes())) {
            return $this->toCustomTimezone($key, $value, (bool)in_array($key, $this->userTimezoneTimes));
        }
        return $value;
    }

    public function toCustomTimezoneIfPossible($key, $value)
    {
        // Set user timezoned dates
        if ($this->isDateObject($key, $value)) {
            if (in_array($key, $this->getAllDates($key))) {
                $value = $this->toCustomTimezone($key, $value);
            } elseif (in_array($key, $this->getAllTimes($key))) {
                $value = $this->timeToCustomTimezone($key, $value);
            }
        }

        return $value;
    }

    /**
     * Overrides getAttributeValue, and convert any dates
     * to the user's timezone.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        if (in_array($key, $this->getAllTimes())) {
            return $this->timeToCustomTimezone($key, $this->convertToDateObject($this->attributes[$key]));
        } elseif (in_array($key, $this->getAllDates())) {
            return $this->toCustomTimezoneIfPossible($key, parent::getAttributeValue($key));
        }
        return parent::getAttributeValue($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if ($value && (in_array($key, $this->getAllDateTimes()))) {
            $value = $this->convertToDateObject($value);
            if ($value instanceof Carbon) {
                // Do this here to convert javascript dates to a usable date
                // The dates mutator will handle converting to date object
                // echo class_basename(get_class($this)).": Trying to set user time for $key to [$value] (".$value->getTimezone()."): Is Carbon Object? (".($value instanceof Carbon).")\n";
                $value = $value->format('Y-m-d H:i:s');
            }
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * @inheritDoc
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        $type = substr($this->getCastType($key), 0, strpos($this->getCastType($key), ':'));
        switch ($type) {
            case 'time':
                $castKey = $this->casts[$key];
                $format = substr($castKey, strpos($castKey, ':') + 1);
                return $this->asTime($value, $format);
            default:
                return parent::castAttribute($key, $value);
        }
    }

    protected function asTime($value, $format = 'H:i:s')
    {
        if ($this->isTimeFormat($value) || $this->isDateTimeFormat($value)) {
            return $this->convertToDateObject($value)->format($format);
        } else {
            return $this->asDateTime($value);
        }
    }

    protected function isTimeFormat($value)
    {
        return DateTimeHelper::isTimeFormat($value);
    }

    protected function isDateTimeFormat($value)
    {
        return DateTimeHelper::isDateTimeFormat($value);
    }

    /**
     * Checks if a date is part of the model's dates array,
     * is an object, and is a Carbon instance.
     *
     * @param $key
     * @param $value
     * @return bool
     */
    public function isDateObject($key, $value)
    {
        return in_array($key, $this->getAllDateTimes()) &&
            is_object($value) &&
            $value instanceof Carbon;
    }

    public function getAllDates(): array
    {
        return array_merge($this->getDates(), $this->userTimezoneDates);
    }

    public function getAllTimes(): array
    {
        return array_merge($this->times, $this->userTimezoneTimes);
    }

    public function getAllDateTimes(): array
    {
        return array_merge($this->getAllDates(), $this->getAllTimes());
    }

    /**
     * Converts a value to a Carbon date object if needed.
     *
     * @param $value
     * @return Carbon
     */
    protected function convertToDateObject($value, $timezone = null)
    {
        return DateTimeHelper::convertTODateObject($value, $timezone);
    }
}
