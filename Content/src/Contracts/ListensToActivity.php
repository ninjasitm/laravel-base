<?php

namespace Nitm\Content\Contracts;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

interface ListensToActivity
{

    /**
     * Prepare an event by collecting data
     */
    public function handle($event);
}
