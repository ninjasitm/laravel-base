<?php

namespace Nitm\Content\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

trait HasCustomDateTimeFormats
{
    /**
     * Laravel uses this method to allow you to initialize traits
     *
     * @return void
     */
    public function initializeHasCustomDateTimeFormats()
    {
        $this->addFillable(
            'date_format',
            'time_format',
            'timezone',
            'timezone_full_format'
        );
    }

    /**
     * Ensure the current timezone value
     *
     * @param Request $request
     */
    public function ensureTimezone($request)
    {
        if ($this->timezone) {
            return;
        }
        // set timezone
        $timezone =  $this->getTimezone($request);
        $this->timezone = $timezone;
        $this->save();
    }

    /**
     * Get the client's Geo IP
     */
    protected function getClientGeoIP(): string
    {
        $ip = \request()->ip();
        return $ip == '127.0.0.1' ? '66.102.0.0' : $ip;
    }

    /**
     * Get the timezone from the request
     *
     * @param Request $request
     *
     * @return string
     */
    protected function getTimezone($request)
    {
        if ($timezone = $request->get('tz')) {
            return $timezone;
        }

        // fetch it from FreeGeoIp
        $ip = $this->getClientGeoIP();

        try {
            $response = json_decode(file_get_contents('http://freegeoip.net/json/' . $ip), true);
            return Arr::get($response, 'time_zone');
        } catch (\Exception $e) {
            return null;
        }
    }
}
