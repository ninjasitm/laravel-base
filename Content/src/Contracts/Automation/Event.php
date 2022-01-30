<?php

namespace Nitm\Content\Contracts\Automation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

interface Event
{

    /**
     * Get the variables supported by this event class
     * @return mixed
     */
    public static function getVariables();

    /**
     * Prepare a message. Can either be a user specified string or the default message string
     *
     * @param string $message The custom user message or the default event message
     */
    public function __($message = null);
}
